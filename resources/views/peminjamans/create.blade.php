<x-layout title="Form Peminjaman">

    <div class="max-w-2xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('bukus.index') }}" class="hover:text-purple-700">📚 Daftar Buku</a>
            <span>›</span>
            <a href="{{ route('bukus.show', $buku) }}" class="hover:text-purple-700 max-w-xs truncate">{{ $buku->judul }}</a>
            <span>›</span>
            <span class="text-gray-900 font-medium">Form Peminjaman</span>
        </nav>

        {{-- Info Buku --}}
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 mb-6">
            <h2 class="text-base font-semibold text-purple-900 mb-3">📖 Informasi Buku</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-gray-500">Judul</dt>
                <dd class="font-medium text-gray-900">{{ $buku->judul }}</dd>
                <dt class="text-gray-500">Penulis</dt>
                <dd class="text-gray-700">{{ $buku->penulis }}</dd>
                <dt class="text-gray-500">ISBN</dt>
                <dd class="font-mono text-gray-700">{{ $buku->isbn }}</dd>
                <dt class="text-gray-500">Stok Tersedia</dt>
                <dd>
                    @if($buku->stok > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                            {{ $buku->stok }} eksemplar
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                            Stok Habis!
                        </span>
                    @endif
                </dd>
            </dl>
        </div>

        @if($buku->stok < 1)
            {{-- Stok Habis: tidak bisa meminjam --}}
            <div class="bg-red-50 border border-red-300 rounded-xl p-6 text-center">
                <p class="text-red-700 font-semibold text-lg mb-2">⚠️ Stok Habis</p>
                <p class="text-red-600 text-sm">Buku ini tidak tersedia untuk dipinjam saat ini.</p>
                <a href="{{ route('bukus.index') }}"
                   class="mt-4 inline-block px-4 py-2 bg-purple-700 text-white text-sm rounded-lg hover:bg-purple-800 transition-colors">
                    ← Kembali ke Daftar Buku
                </a>
            </div>
        @else
            {{-- Form Peminjaman --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h1 class="text-xl font-bold text-gray-900 mb-5">📋 Form Peminjaman Buku</h1>

                <form action="{{ route('peminjamans.store', $buku) }}" method="POST" novalidate>
                    @csrf

                    {{-- NIM --}}
                    <div class="mb-4">
                        <label for="nim_peminjam" class="block text-sm font-medium text-gray-700 mb-1">
                            NIM Peminjam <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="nim_peminjam"
                            name="nim_peminjam"
                            value="{{ old('nim_peminjam') }}"
                            placeholder="Contoh: 12S24028"
                            maxlength="20"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('nim_peminjam') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus:border-purple-500' }}"
                        >
                        @error('nim_peminjam')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama --}}
                    <div class="mb-4">
                        <label for="nama_peminjam" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Peminjam <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="nama_peminjam"
                            name="nama_peminjam"
                            value="{{ old('nama_peminjam') }}"
                            placeholder="Nama lengkap mahasiswa"
                            maxlength="150"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('nama_peminjam') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus:border-purple-500' }}"
                        >
                        @error('nama_peminjam')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Pinjam --}}
                    <div class="mb-4">
                        <label for="tanggal_pinjam" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Pinjam <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="tanggal_pinjam"
                            name="tanggal_pinjam"
                            value="{{ old('tanggal_pinjam', now()->toDateString()) }}"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('tanggal_pinjam') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus:border-purple-500' }}"
                        >
                        @error('tanggal_pinjam')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Jatuh Tempo --}}
                    <div class="mb-6">
                        <label for="tanggal_jatuh_tempo" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="tanggal_jatuh_tempo"
                            name="tanggal_jatuh_tempo"
                            value="{{ old('tanggal_jatuh_tempo', now()->addDays(7)->toDateString()) }}"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('tanggal_jatuh_tempo') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus:border-purple-500' }}"
                        >
                        @error('tanggal_jatuh_tempo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Default: 7 hari dari tanggal pinjam.</p>
                    </div>

                    {{-- Catatan ACID --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-5 text-xs text-blue-700">
                        🔒 <strong>ACID Transaction:</strong> Proses peminjaman menggunakan <code>DB::transaction()</code> dan
                        <code>lockForUpdate()</code> untuk menjamin konsistensi stok secara konkuren.
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 py-3 px-4 bg-purple-900 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                            ✅ Proses Peminjaman
                        </button>
                        <a href="{{ route('bukus.index') }}"
                           class="px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        @endif

    </div>

</x-layout>
