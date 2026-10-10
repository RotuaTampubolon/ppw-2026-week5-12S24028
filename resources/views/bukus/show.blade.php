<x-layout title="Detail Buku">

    <div class="mb-6 flex justify-between items-end">
        <div>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <a href="{{ route('bukus.index') }}" class="hover:text-purple-700">Daftar Buku</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900 font-medium">Detail Buku</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">📖 {{ $buku->judul }}</h1>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('bukus.edit', $buku) }}"
               class="px-4 py-2 bg-yellow-100 text-yellow-800 hover:bg-yellow-200 font-medium rounded-lg text-sm transition-colors">
                ✏️ Edit Buku
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900">Informasi Buku</h3>
        </div>
        
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">ISBN</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $buku->isbn }}</dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $buku->kategori->nama_kategori ?? 'Tidak Ada Kategori' }}
                        </span>
                    </dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Penulis</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $buku->penulis }}</dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Penerbit</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $buku->penerbit }}</dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Tahun Terbit</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $buku->tahun_terbit }}</dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Stok Tersedia</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $buku->stok > 5 ? 'bg-green-100 text-green-800' : ($buku->stok > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $buku->stok }} exemplar
                        </span>
                    </dd>
                </div>
                
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Sinopsis</dt>
                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $buku->sinopsis ?: 'Tidak ada sinopsis tersedia untuk buku ini.' }}</dd>
                </div>
                
                <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                    <dt class="text-xs font-medium text-gray-400">Terakhir Diperbarui</dt>
                    <dd class="mt-1 text-xs text-gray-500">{{ $buku->updated_at->format('d M Y, H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
    
    <div class="mt-6 flex">
        <a href="{{ route('bukus.index') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
            &larr; Kembali ke Daftar Buku
        </a>
    </div>

</x-layout>
