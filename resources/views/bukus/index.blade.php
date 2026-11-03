<x-layout title="Katalog Buku">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📚 Katalog Buku & Statistik Koleksi</h1>
            <p class="text-sm text-gray-500 mt-1">Sistem Informasi Perpustakaan Kampus Del — SIPUS-Del</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('peminjamans.create') }}"
               class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Pinjam Buku
            </a>
            <a href="{{ route('bukus.create') }}"
               class="inline-flex items-center px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Buku
            </a>
        </div>
    </div>

    {{-- Kartu Statistik Perpustakaan (Query Builder DB::table) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-purple-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Judul Buku</p>
                <p class="text-2xl font-extrabold text-purple-900 mt-1">{{ number_format($stats['total_judul']) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Koleksi terdaftar di sistem</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 text-purple-700 rounded-xl flex items-center justify-center text-xl font-bold">
                📖
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-purple-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Stok Tersedia</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['total_stok']) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Eksemplar siap dipinjam</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center text-xl font-bold">
                📦
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-purple-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Sedang Dipinjam</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['total_dipinjam']) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Eksemplar di tangan mahasiswa</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center text-xl font-bold">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-purple-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Dikembalikan</p>
                <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['total_dikembalikan']) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Transaksi selesai/sukses</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center text-xl font-bold">
                ✅
            </div>
        </div>
    </div>

    {{-- Filter & Dynamic Search Bar --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" action="{{ route('bukus.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari berdasarkan judul, penulis, atau ISBN..."
                       class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="w-full md:w-64">
                <select name="kategori_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ (string)$kategoriId === (string)$kat->id ? 'selected' : '' }}>
                            {{ $kat->kode_kategori }} — {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                    🔍 Filter
                </button>
                @if($search || $kategoriId)
                    <a href="{{ route('bukus.index') }}"
                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </div>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Judul & Penerbit</th>
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
                        <div class="text-sm font-semibold text-gray-900 max-w-xs truncate">{{ $buku->judul }}</div>
                        <div class="text-xs text-gray-500">{{ $buku->penerbit }} ({{ $buku->tahun_terbit }})</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $buku->penulis }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $buku->kategori->nama_kategori ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold
                            {{ $buku->stok > 3 ? 'bg-green-100 text-green-800' : ($buku->stok > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $buku->stok }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $buku->peminjamans_count ?? 0 }}x
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-1.5">
                            @if($buku->stok > 0)
                                <a href="{{ route('peminjamans.create', ['buku_id' => $buku->id]) }}"
                                   class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded shadow-sm transition-colors">
                                    Pinjam
                                </a>
                            @else
                                <span class="px-2 py-1 bg-gray-200 text-gray-400 text-xs font-medium rounded cursor-not-allowed">
                                    Habis
                                </span>
                            @endif
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
                <p class="text-lg font-medium">Buku tidak ditemukan.</p>
                <p class="text-sm mt-1">Coba ubah kata kunci pencarian atau filter kategori Anda.</p>
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
