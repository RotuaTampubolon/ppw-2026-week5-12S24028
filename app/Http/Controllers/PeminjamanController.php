<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan daftar seluruh peminjaman.
     * Menggunakan Query Builder (DB::table) untuk statistik agregat.
     */
    public function index(): View
    {
        // Query Builder — statistik dashboard
        $stats = [
            'total_judul'        => DB::table('bukus')->count(),
            'total_stok'         => DB::table('bukus')->sum('stok'),
            'total_dipinjam'     => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        $peminjamans = Peminjaman::with('buku')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('peminjamans.index', compact('peminjamans', 'stats'));
    }

    /**
     * Tampilkan form peminjaman buku tertentu.
     */
    public function create(Buku $buku): View
    {
        return view('peminjamans.create', compact('buku'));
    }

    /**
     * Simpan peminjaman baru dengan ACID Transaction + lockForUpdate().
     * Menjamin atomicity, consistency, isolation, dan durability.
     *
     * @throws \RuntimeException ketika stok habis atau NIM sudah meminjam buku yang sama
     */
    public function store(Request $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validate([
            'nim_peminjam'        => 'required|string|max:20',
            'nama_peminjam'       => 'required|string|max:150',
            'tanggal_pinjam'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_pinjam',
        ], [
            'nim_peminjam.required'        => 'NIM peminjam wajib diisi.',
            'nama_peminjam.required'       => 'Nama peminjam wajib diisi.',
            'tanggal_pinjam.required'      => 'Tanggal pinjam wajib diisi.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tanggal_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.',
        ]);

        try {
            DB::transaction(function () use ($validated, $buku) {
                // ACID — Isolation: kunci baris buku agar tidak ada race condition
                $bukuLocked = Buku::where('id', $buku->id)->lockForUpdate()->firstOrFail();

                // Consistency check: stok harus >= 1
                if ($bukuLocked->stok < 1) {
                    throw new \RuntimeException('Stok buku "' . $bukuLocked->judul . '" sudah habis. Peminjaman tidak dapat diproses.');
                }

                // Consistency check: satu NIM tidak boleh meminjam buku yang sama lebih dari sekali secara aktif
                $sudahMeminjam = Peminjaman::where('buku_id', $bukuLocked->id)
                    ->where('nim_peminjam', $validated['nim_peminjam'])
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sudahMeminjam) {
                    throw new \RuntimeException('NIM ' . $validated['nim_peminjam'] . ' masih memiliki peminjaman aktif untuk buku ini.');
                }

                // Generate kode peminjaman unik
                $kodePinjam = 'PJM-' . strtoupper(substr(md5(uniqid()), 0, 8));

                // Atomicity: kurangi stok dan buat record peminjaman dalam satu transaksi
                $bukuLocked->decrement('stok');

                Peminjaman::create([
                    'kode_pinjam'         => $kodePinjam,
                    'buku_id'             => $bukuLocked->id,
                    'nim_peminjam'        => $validated['nim_peminjam'],
                    'nama_peminjam'       => $validated['nama_peminjam'],
                    'tanggal_pinjam'      => $validated['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status'              => 'dipinjam',
                ]);
            }, 3); // Retry 3 kali jika deadlock

            return redirect()->route('peminjamans.index')
                ->with('success', 'Peminjaman buku berhasil dicatat! Stok buku telah dikurangi.');

        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PEMINJAMAN]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan basis data pada transaksi peminjaman.');
        } catch (\Throwable $e) {
            Log::error('[SYSTEM EXCEPTION - PEMINJAMAN]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Proses pengembalian buku dengan ACID Transaction + lockForUpdate().
     * Durability: perubahan status dan stok bersifat permanen setelah commit.
     */
    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                // ACID — Isolation: kunci baris peminjaman
                $peminjamanLocked = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                // Consistency check: hanya peminjaman aktif yang bisa dikembalikan
                if ($peminjamanLocked->status === 'dikembalikan') {
                    throw new \RuntimeException('Buku ini sudah pernah dikembalikan sebelumnya.');
                }

                // ACID — Isolation: kunci baris buku yang bersangkutan
                $buku = Buku::where('id', $peminjamanLocked->buku_id)->lockForUpdate()->firstOrFail();

                // Atomicity: update status peminjaman dan kembalikan stok dalam satu transaksi
                $peminjamanLocked->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                $buku->increment('stok');
            }, 3); // Retry 3 kali jika deadlock

            return redirect()->route('peminjamans.index')
                ->with('success', 'Pengembalian buku berhasil dicatat! Stok buku telah dikembalikan.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PENGEMBALIAN]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan basis data pada transaksi pengembalian.');
        } catch (\Throwable $e) {
            Log::error('[SYSTEM EXCEPTION - PENGEMBALIAN]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
