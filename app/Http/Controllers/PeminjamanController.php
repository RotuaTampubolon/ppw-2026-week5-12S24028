<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan daftar transaksi sirkulasi peminjaman & pengembalian buku.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $peminjamans = Peminjaman::with('buku.kategori')
            ->when($status && in_array($status, ['dipinjam', 'dikembalikan']), function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($search, function ($q, $s) {
                return $q->where(function ($sub) use ($s) {
                    $sub->where('kode_pinjam', 'like', "%{$s}%")
                        ->orWhere('nim_peminjam', 'like', "%{$s}%")
                        ->orWhere('nama_peminjam', 'like', "%{$s}%")
                        ->orWhereHas('buku', function ($bukuQuery) use ($s) {
                            $bukuQuery->where('judul', 'like', "%{$s}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('peminjamans.index', compact('peminjamans', 'status', 'search'));
    }

    /**
     * Form peminjaman buku baru.
     */
    public function create(Request $request): View
    {
        $selectedBukuId = $request->query('buku_id');
        $bukus = Buku::where('stok', '>', 0)->orderBy('judul')->get();
        $selectedBuku = $selectedBukuId ? Buku::find($selectedBukuId) : null;

        return view('peminjamans.create', compact('bukus', 'selectedBuku'));
    }

    /**
     * Memproses transaksi peminjaman buku secara ACID.
     * Menggunakan DB::transaction() dan lockForUpdate() (Pessimistic Locking)
     * untuk mencegah Race Condition stok dan mencegah NIM ganda meminjam buku aktif.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id'       => 'required|integer|exists:bukus,id',
            'nim_peminjam'  => 'required|string|min:6|max:20',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'durasi_hari'   => 'nullable|integer|min:1|max:30',
        ], [
            'buku_id.required'       => 'Pilih buku yang ingin dipinjam.',
            'buku_id.exists'         => 'Buku yang dipilih tidak valid.',
            'nim_peminjam.required'  => 'NIM peminjam wajib diisi.',
            'nim_peminjam.min'       => 'NIM peminjam minimal 6 karakter.',
            'nama_peminjam.required' => 'Nama peminjam wajib diisi.',
        ]);

        $nim = strtoupper(trim($validated['nim_peminjam']));
        $nama = trim($validated['nama_peminjam']);
        $durasi = (int) ($validated['durasi_hari'] ?? 7);

        try {
            DB::transaction(function () use ($validated, $nim, $nama, $durasi) {
                // 1. Kunci baris buku dengan Pessimistic Locking (SELECT ... FOR UPDATE)
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                // 2. Verifikasi stok buku masih tersedia (Atomicity & Consistency)
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Stok buku '{$buku->judul}' telah habis (0). Peminjaman dibatalkan.");
                }

                // 3. Cegah NIM yang sama meminjam buku yang sama jika belum dikembalikan
                $sedangDipinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sedangDipinjam) {
                    throw new \RuntimeException("Mahasiswa NIM {$nim} masih meminjam buku '{$buku->judul}' dan belum mengembalikannya!");
                }

                // 4. Kurangi stok buku secara atomik
                $buku->decrement('stok');

                // 5. Catat transaksi ke tabel peminjamans
                $kodePinjam = 'PINJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));
                $tanggalPinjam = now()->toDateString();
                $tanggalJatuhTempo = now()->addDays($durasi)->toDateString();

                Peminjaman::create([
                    'kode_pinjam'         => $kodePinjam,
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => $nim,
                    'nama_peminjam'       => $nama,
                    'tanggal_pinjam'      => $tanggalPinjam,
                    'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                    'status'              => 'dipinjam',
                ]);

                // Simpan state session peminjam terakhir untuk kenyamanan pustakawan
                session([
                    'last_borrower_nim'  => $nim,
                    'last_borrower_name' => $nama,
                ]);
            }, 3);

            return redirect()->route('peminjamans.index')
                ->with('success', 'Transaksi peminjaman buku berhasil dicatat secara ACID!');
        } catch (\RuntimeException $e) {
            Log::warning('[PEMINJAMAN REJECTED]: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION PEMINJAMAN]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem basis data saat memproses peminjaman.');
        } catch (\Exception $e) {
            Log::error('[GENERAL EXCEPTION PEMINJAMAN]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Memproses transaksi pengembalian buku secara ACID.
     * Menggunakan DB::transaction() dan lockForUpdate() untuk mengubah status
     * dan mengembalikan stok buku secara atomik.
     */
    public function kembali(int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Kunci baris peminjaman
                $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku ini sudah berstatus dikembalikan sebelumnya.");
                }

                // 2. Kunci baris buku terkait
                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->firstOrFail();

                // 3. Update status peminjaman dan catat tanggal kembali
                $peminjaman->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Tambah kembali stok buku secara atomik
                $buku->increment('stok');
            }, 3);

            return back()->with('success', 'Buku berhasil dikembalikan dan stok telah dipulihkan secara atomik!');
        } catch (\RuntimeException $e) {
            Log::warning('[PENGEMBALIAN REJECTED]: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION PENGEMBALIAN]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada basis data saat memproses pengembalian.');
        } catch (\Exception $e) {
            Log::error('[GENERAL EXCEPTION PENGEMBALIAN]: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}
