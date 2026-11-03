<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menggunakan Eager Loading with('kategori'), withCount('peminjamans'),
     * Query Builder untuk statistik agregat, dan dynamic filter dengan when().
     */
    public function index(Request $request): View
    {
        $search     = $request->input('search', '');
        $kategoriId = $request->input('kategori_id', '');

        // Simpan pencarian terakhir ke Session
        if ($search) {
            session(['last_buku_search' => $search]);
        }

        // Query Builder — statistik perpustakaan (tanpa Eloquent ORM)
        $stats = [
            'total_judul'        => DB::table('bukus')->count(),
            'total_stok'         => DB::table('bukus')->sum('stok'),
            'total_dipinjam'     => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        // Eager Loading + withCount + dynamic filter dengan when()
        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($search, fn ($q, $s) => $q->where(function ($inner) use ($s) {
                $inner->where('judul', 'like', "%{$s}%")
                      ->orWhere('penulis', 'like', "%{$s}%")
                      ->orWhere('isbn', 'like', "%{$s}%");
            }))
            ->when($kategoriId, fn ($q, $k) => $q->where('kategori_id', $k))
            ->orderBy('judul')
            ->paginate(10)
            ->withQueryString();

        $kategoris      = Kategori::orderBy('nama_kategori')->get();
        $lastBukuSearch = session('last_buku_search');

        return view('bukus.index', compact('bukus', 'stats', 'kategoris', 'search', 'kategoriId', 'lastBukuSearch'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('bukus.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'judul'        => 'required|string|min:5|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . date('Y'),
            'kategori_id'  => 'required|integer|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ], $this->messages());

        Buku::create($validated);

        return redirect()->route('bukus.index')
            ->with('success', 'Buku berhasil ditambahkan ke perpustakaan!');
    }

    /**
     * Display the specified resource.
     * Implicit Route Model Binding - Laravel otomatis resolve Buku dari {buku}.
     */
    public function show(Buku $buku): View
    {
        $buku->load('kategori');

        return view('bukus.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Buku $buku): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('bukus.edit', compact('buku', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn,' . $buku->id,
            'judul'        => 'required|string|min:5|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . date('Y'),
            'kategori_id'  => 'required|integer|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ], $this->messages());

        $buku->update($validated);

        return redirect()->route('bukus.index')
            ->with('success', 'Data buku berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Buku $buku): RedirectResponse
    {
        $buku->delete();

        return redirect()->route('bukus.index')
            ->with('success', 'Buku berhasil dihapus dari perpustakaan!');
    }

    /**
     * Pesan validasi kustom dalam Bahasa Indonesia.
     */
    private function messages(): array
    {
        return [
            'isbn.required'         => 'ISBN wajib diisi.',
            'isbn.unique'           => 'ISBN sudah terdaftar di perpustakaan.',
            'isbn.max'              => 'ISBN maksimal 20 karakter.',
            'judul.required'        => 'Judul buku wajib diisi.',
            'judul.min'             => 'Judul buku minimal 5 karakter.',
            'judul.max'             => 'Judul buku maksimal 255 karakter.',
            'penulis.required'      => 'Nama penulis wajib diisi.',
            'penerbit.required'     => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.integer'  => 'Tahun terbit harus berupa angka.',
            'tahun_terbit.min'      => 'Tahun terbit minimal tahun 1900.',
            'tahun_terbit.max'      => 'Tahun terbit tidak boleh melebihi tahun ini.',
            'kategori_id.required'  => 'Kategori wajib dipilih.',
            'kategori_id.exists'    => 'Kategori yang dipilih tidak valid.',
            'stok.required'         => 'Stok wajib diisi.',
            'stok.integer'          => 'Stok harus berupa angka bulat.',
            'stok.min'              => 'Stok tidak boleh negatif.',
        ];
    }
}
