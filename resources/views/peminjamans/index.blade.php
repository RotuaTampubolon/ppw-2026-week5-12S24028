<x-layout title="Sirkulasi Peminjaman Buku">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🔄 Sirkulasi Peminjaman & Pengembalian</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola transaksi peminjaman buku dengan transaksi ACID & Pessimistic Locking</p>
        </div>
        <div>
            <a href="{{ route('peminjamans.create') }}"
               class="inline-flex items-center px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                + Catat Peminjaman Baru
            </a>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" action="{{ route('peminjamans.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status:</span>
                <a href="{{ route('peminjamans.index', array_merge(request()->except('status', 'page'), ['status' => ''])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium {{ empty($status) ? 'bg-purple-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Semua
                </a>
                <a href="{{ route('peminjamans.index', array_merge(request()->except('status', 'page'), ['status' => 'dipinjam'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === 'dipinjam' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    ⏳ Sedang Dipinjam
                </a>
                <a href="{{ route('peminjamans.index', array_merge(request()->except('status', 'page'), ['status' => 'dikembalikan'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === 'dikembalikan' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    ✅ Dikembalikan
                </a>
            </div>

            <div class="flex items-center gap-2 w-full md:w-80">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari kode, NIM, nama, judul..."
                       class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white text-xs font-medium rounded-lg hover:bg-gray-700">
                    Cari
                </button>
                @if($search || $status)
                    <a href="{{ route('peminjamans.index') }}" class="text-xs text-gray-500 hover:text-red-600 whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse($peminjamans as $pinjam)
            @if($loop->first)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kode Pinjam</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Peminjam</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Buku</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Tgl Pinjam</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
            @endif

                <tr class="hover:bg-purple-50 transition-colors">
                    <td class="px-4 py-3 text-sm font-mono font-bold text-purple-900">
                        {{ $pinjam->kode_pinjam }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-900">{{ $pinjam->nama_peminjam }}</div>
                        <div class="text-xs font-mono text-gray-500">NIM: {{ $pinjam->nim_peminjam }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900 max-w-xs truncate">{{ $pinjam->buku->judul ?? 'Buku Dihapus' }}</div>
                        <div class="text-xs text-gray-500">ISBN: {{ $pinjam->buku->isbn ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-600">
                        {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-600">
                        {{ \Carbon\Carbon::parse($pinjam->tanggal_jatuh_tempo)->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($pinjam->status === 'dipinjam')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                ⏳ Dipinjam
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                ✅ Dikembalikan
                            </span>
                            @if($pinjam->tanggal_kembali)
                                <div class="text-[10px] text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($pinjam->tanggal_kembali)->format('d M Y') }}
                                </div>
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($pinjam->status === 'dipinjam')
                            <form action="{{ route('peminjamans.kembali', $pinjam->id) }}" method="POST"
                                  onsubmit="return confirm('Konfirmasi pengembalian buku \'{{ addslashes($pinjam->buku->judul ?? '') }}\'? Stok buku akan bertambah kembali secara otomatis.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md shadow-sm transition-colors">
                                    Kembalikan
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400 italic">Selesai</span>
                        @endif
                    </td>
                </tr>

            @if($loop->last)
                    </tbody>
                </table>
            @endif

        @empty
            <div class="py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="font-medium">Tidak ada data transaksi peminjaman.</p>
                <p class="text-xs text-gray-400 mt-1">Gunakan tombol di atas untuk mencatat peminjaman baru.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($peminjamans->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $peminjamans->links() }}
        </div>
    @endif

</x-layout>
