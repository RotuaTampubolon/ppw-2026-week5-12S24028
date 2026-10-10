<x-layout title="Detail Kategori">

    <div class="mb-6 flex justify-between items-end">
        <div>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <a href="{{ route('kategoris.index') }}" class="hover:text-purple-700">Daftar Kategori</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900 font-medium">Detail Kategori</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">🗂️ Kategori: {{ $kategori->nama_kategori }}</h1>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('kategoris.edit', $kategori) }}"
               class="px-4 py-2 bg-yellow-100 text-yellow-800 hover:bg-yellow-200 font-medium rounded-lg text-sm transition-colors">
                ✏️ Edit Kategori
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="block text-sm font-medium text-gray-500">Kode Kategori</span>
                <span class="block mt-1 font-mono font-bold text-gray-900">{{ $kategori->kode_kategori }}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-500">Nama Kategori</span>
                <span class="block mt-1 text-gray-900">{{ $kategori->nama_kategori }}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-500">Jumlah Buku</span>
                <span class="block mt-1 text-gray-900">{{ $kategori->bukus->count() }} judul buku</span>
            </div>
        </div>
    </div>

    {{-- Daftar Buku dalam Kategori --}}
    <h2 class="text-xl font-bold text-gray-900 mb-4">Buku dalam Kategori Ini</h2>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse($kategori->bukus as $buku)
            @if($loop->first)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ISBN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penulis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
            @endif

                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $buku->isbn }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-purple-700 hover:underline">
                        <a href="{{ route('bukus.show', $buku) }}">{{ $buku->judul }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $buku->penulis }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $buku->tahun_terbit }}</td>
                </tr>

            @if($loop->last)
                    </tbody>
                </table>
            @endif

        @empty
            <div class="p-6 text-center text-gray-500">
                Belum ada buku dalam kategori ini.
            </div>
        @endforelse
    </div>

</x-layout>
