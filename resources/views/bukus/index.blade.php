<x-layout title="Daftar Buku">

    {{-- Stats Cards (Query Builder) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-purple-700">{{ number_format($stats['total_judul']) }}</p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Total Judul</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_stok']) }}</p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Total Stok</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-yellow-600">{{ number_format($stats['total_dipinjam']) }}</p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Sedang Dipinjam</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-green-600">{{ number_format($stats['total_dikembalikan']) }}</p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Dikembalikan</p>
        </div>
    </div>

    {{-- Header + Tambah Buku --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📚 Daftar Buku Perpustakaan</h1>
            <p class="text-sm text-gray-500 mt-1">Total: {{ $bukus->total() }} buku ditemukan</p>
        </div>
        @auth
        <a href="{{ route('bukus.create') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Buku
        </a>
        @endauth
    </div>

    {{-- Session: Pencarian Terakhir --}}
    @if(session('last_buku_search'))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            🕐 Pencarian terakhir Anda: <strong>{{ session('last_buku_search') }}</strong>
        </div>
    @endif

    {{-- Form Filter & Pencarian --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form action="{{ route('bukus.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari judul, penulis, atau ISBN..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
            </div>
            <div class="sm:w-52">
                <select name="kategori_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">— Semua Kategori —</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-5 py-2 bg-purple-700 text-white text-sm font-medium rounded-lg hover:bg-purple-800 transition-colors">
                🔍 Cari
            </button>
            @if($search || $kategoriId)
                <a href="{{ route('bukus.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors text-center">
                    ✕ Reset
                </a>
            @endif
        </form>
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
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Stok</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Dipinjam</th>
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
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                            {{ $buku->stok > 5 ? 'bg-green-100 text-green-800' : ($buku->stok > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $buku->stok }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        {{-- withCount('peminjamans') --}}
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                            {{ $buku->peminjamans_count }}×
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-1">
                            <a href="{{ route('bukus.show', $buku) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded hover:bg-blue-50">
                                Detail
                            </a>
                            @auth
                            <a href="{{ route('peminjamans.create', $buku) }}"
                               class="text-green-600 hover:text-green-800 text-xs font-medium px-2 py-1 rounded hover:bg-green-50">
                                Pinjam
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
                            @endauth
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
                <p class="text-lg font-medium">Tidak ada buku yang sesuai pencarian.</p>
                <p class="text-sm mt-1">Coba kata kunci lain atau <a href="{{ route('bukus.index') }}" class="text-purple-600 underline">reset filter</a>.</p>
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
