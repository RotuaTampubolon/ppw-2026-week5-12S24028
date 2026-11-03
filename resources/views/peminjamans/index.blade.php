<x-layout title="Data Peminjaman">

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

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📋 Data Peminjaman Buku</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $peminjamans->total() }} record peminjaman</p>
        </div>
        <a href="{{ route('bukus.index') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-lg shadow transition-colors">
            📚 Pilih Buku untuk Dipinjam
        </a>
    </div>

    {{-- Tabel Peminjaman --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        @forelse($peminjamans as $p)
            @if($loop->first)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Buku</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">NIM</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tgl Pinjam</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Dikembalikan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
            @endif

                <tr class="hover:bg-purple-50 transition-colors">
                    <td class="px-4 py-3 text-xs font-mono text-gray-600">{{ $p->kode_pinjam }}</td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900 max-w-[200px] truncate">
                            {{ $p->buku->judul ?? 'N/A' }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $p->nim_peminjam }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $p->nama_peminjam }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $p->tanggal_pinjam->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <span class="{{ $p->status === 'dipinjam' && $p->tanggal_jatuh_tempo->isPast() ? 'text-red-600 font-semibold' : '' }}">
                            {{ $p->tanggal_jatuh_tempo->format('d/m/Y') }}
                        </span>
                        @if($p->status === 'dipinjam' && $p->tanggal_jatuh_tempo->isPast())
                            <span class="ml-1 text-xs text-red-500">⚠ Terlambat</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $p->tanggal_kembali ? $p->tanggal_kembali->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($p->status === 'dipinjam')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                📖 Dipinjam
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✅ Dikembalikan
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($p->status === 'dipinjam')
                            <form action="{{ route('peminjamans.kembalikan', $p) }}" method="POST"
                                  onsubmit="return confirm('Konfirmasi pengembalian buku dari {{ addslashes($p->nama_peminjam) }}?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="text-green-600 hover:text-green-800 text-xs font-medium px-3 py-1.5 rounded border border-green-300 hover:bg-green-50 transition-colors">
                                    ↩ Kembalikan
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
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <p class="text-lg font-medium">Belum ada data peminjaman.</p>
                <p class="text-sm mt-1">Pilih buku dari <a href="{{ route('bukus.index') }}" class="text-purple-600 underline">Daftar Buku</a> dan klik "Pinjam".</p>
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
