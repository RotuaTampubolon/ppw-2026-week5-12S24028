<x-layout title="Daftar Kategori">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🗂️ Kategori Buku</h1>
            <p class="text-sm text-gray-500 mt-1">Total: {{ $kategoris->total() }} kategori terdaftar</p>
        </div>
        <a href="{{ route('kategoris.create') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </a>
    </div>

    {{-- Tabel Kategori --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse($kategoris as $kategori)
            @if($loop->first)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama Kategori</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Jumlah Buku</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
            @endif

                <tr class="hover:bg-purple-50 transition-colors">
                    <td class="px-4 py-3 text-sm text-gray-500 w-16">
                        {{ ($kategoris->currentPage() - 1) * $kategoris->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-700 font-bold">{{ $kategori->kode_kategori }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $kategori->nama_kategori }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $kategori->bukus_count ?? 0 }} buku
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center w-48">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('kategoris.show', $kategori) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded hover:bg-blue-50">
                                Detail
                            </a>
                            <a href="{{ route('kategoris.edit', $kategori) }}"
                               class="text-yellow-600 hover:text-yellow-800 text-xs font-medium px-2 py-1 rounded hover:bg-yellow-50">
                                Edit
                            </a>
                            <form action="{{ route('kategoris.destroy', $kategori) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus kategori \'{{ addslashes($kategori->nama_kategori) }}\'?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50"
                                        {{ ($kategori->bukus_count ?? 0) > 0 ? 'disabled title="Tidak bisa dihapus karena masih ada buku"' : '' }}>
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
                <p class="text-lg font-medium">Belum ada kategori terdaftar.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($kategoris->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $kategoris->links() }}
        </div>
    @endif

</x-layout>
