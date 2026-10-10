<x-layout title="Daftar Buku">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📚 Daftar Buku Perpustakaan</h1>
            <p class="text-sm text-gray-500 mt-1">Total: {{ $bukus->total() }} buku terdaftar</p>
        </div>
        <a href="{{ route('bukus.create') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Buku
        </a>
    </div>

    {{-- Tabel Buku --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse($bukus as $buku)
            @if($loop->first)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">ISBN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Judul</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Penulis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tahun</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Stok</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
            @endif

                <tr class="hover:bg-purple-50 transition-colors">
                    <td class="px-4 py-3 text-sm text-gray-500">
                        {{ ($bukus->currentPage() - 1) * $bukus->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $buku->isbn }}</td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900 max-w-xs truncate">{{ $buku->judul }}</div>
                        <div class="text-xs text-gray-500">{{ $buku->penerbit }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $buku->penulis }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $buku->kategori->nama_kategori ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $buku->tahun_terbit }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                            {{ $buku->stok > 5 ? 'bg-green-100 text-green-800' : ($buku->stok > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $buku->stok }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('bukus.show', $buku) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded hover:bg-blue-50">
                                Detail
                            </a>
                            <a href="{{ route('bukus.edit', $buku) }}"
                               class="text-yellow-600 hover:text-yellow-800 text-xs font-medium px-2 py-1 rounded hover:bg-yellow-50">
                                Edit
                            </a>
                            <form action="{{ route('bukus.destroy', $buku) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus buku \'{{ addslashes($buku->judul) }}\'?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

            @if($loop->last)
                    </tbody>
                </table>
            @endif

        @empty
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="text-lg font-medium">Belum ada buku terdaftar.</p>
                <p class="text-sm mt-1">Mulai dengan menambahkan buku pertama.</p>
                <a href="{{ route('bukus.create') }}"
                   class="mt-4 px-4 py-2 bg-purple-700 text-white text-sm rounded-lg hover:bg-purple-800 transition-colors">
                    + Tambah Buku Pertama
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($bukus->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $bukus->links() }}
        </div>
    @endif

</x-layout>
