<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $kategoris = Kategori::withCount('bukus')->orderBy('kode_kategori')->paginate(10);

        return view('kategoris.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('kategoris.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:10|unique:kategoris,kode_kategori|alpha_dash',
            'nama_kategori' => 'required|string|max:100',
        ], [
            'kode_kategori.required'   => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'     => 'Kode kategori sudah digunakan.',
            'kode_kategori.max'        => 'Kode kategori maksimal 10 karakter.',
            'kode_kategori.alpha_dash' => 'Kode kategori hanya boleh huruf, angka, dan tanda hubung.',
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.max'        => 'Nama kategori maksimal 100 karakter.',
        ]);

        Kategori::create($validated);

        return redirect()->route('kategoris.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kategori $kategori): View
    {
        // Eager load bukus untuk tampilan detail
        $kategori->load('bukus');

        return view('kategoris.show', compact('kategori'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori): View
    {
        return view('kategoris.edit', compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:10|alpha_dash|unique:kategoris,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|max:100',
        ], [
            'kode_kategori.required'   => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'     => 'Kode kategori sudah digunakan.',
            'kode_kategori.max'        => 'Kode kategori maksimal 10 karakter.',
            'kode_kategori.alpha_dash' => 'Kode kategori hanya boleh huruf, angka, dan tanda hubung.',
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.max'        => 'Nama kategori maksimal 100 karakter.',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategoris.index')
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->bukus()->count() > 0) {
            return redirect()->route('kategoris.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki buku terkait!');
        }

        $kategori->delete();

        return redirect()->route('kategoris.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}
