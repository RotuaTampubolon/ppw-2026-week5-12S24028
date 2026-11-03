<x-layout title="Catat Peminjaman Buku">

    <div class="mb-6">
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('peminjamans.index') }}" class="hover:text-purple-700">Sirkulasi</a>
            <span class="mx-2">›</span>
            <span class="text-gray-900 font-medium">Catat Peminjaman Baru</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">➕ Catat Peminjaman Buku (Transaksi ACID)</h1>
        <p class="text-sm text-gray-500">Peminjaman diproses dengan mekanisme <code>lockForUpdate()</code> untuk menjaga konsistensi stok.</p>
    </div>

    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg">
                <h3 class="text-sm font-semibold text-red-800 mb-2">⚠️ Harap perbaiki kesalahan berikut:</h3>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('peminjamans.store') }}" method="POST">
            @csrf

            <div class="space-y-5">
                {{-- Pilihan Buku --}}
                <div>
                    <label for="buku_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Buku yang Dipinjam <span class="text-red-500">*</span>
                    </label>
                    <select id="buku_id" name="buku_id" required
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 {{ $errors->has('buku_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        <option value="">-- Pilih Buku Tersedia --</option>
                        @foreach($bukus as $buku)
                            <option value="{{ $buku->id }}"
                                {{ (old('buku_id', $selectedBuku->id ?? null) == $buku->id) ? 'selected' : '' }}>
                                {{ $buku->judul }} (Stok: {{ $buku->stok }}) — {{ $buku->penulis }}
                            </option>
                        @endforeach
                    </select>
                    @error('buku_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NIM Peminjam --}}
                <div>
                    <label for="nim_peminjam" class="block text-sm font-medium text-gray-700 mb-1">
                        NIM Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nim_peminjam" name="nim_peminjam"
                           value="{{ old('nim_peminjam', session('last_borrower_nim')) }}"
                           placeholder="Contoh: 12S24028" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 uppercase {{ $errors->has('nim_peminjam') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    <p class="text-xs text-gray-400 mt-1">Sistem akan memvalidasi agar 1 mahasiswa tidak meminjam 2 eksemplar buku yang sama secara bersamaan.</p>
                    @error('nim_peminjam')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Peminjam --}}
                <div>
                    <label for="nama_peminjam" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_peminjam" name="nama_peminjam"
                           value="{{ old('nama_peminjam', session('last_borrower_name')) }}"
                           placeholder="Nama lengkap mahasiswa" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 {{ $errors->has('nama_peminjam') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('nama_peminjam')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Durasi Peminjaman --}}
                <div>
                    <label for="durasi_hari" class="block text-sm font-medium text-gray-700 mb-1">
                        Durasi Pinjam (Hari)
                    </label>
                    <input type="number" id="durasi_hari" name="durasi_hari"
                           value="{{ old('durasi_hari', 7) }}" min="1" max="30"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 {{ $errors->has('durasi_hari') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    <p class="text-xs text-gray-400 mt-1">Standar peminjaman kampus Del adalah 7 hari kalender.</p>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('peminjamans.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg shadow transition-colors">
                    🔒 Proses Peminjaman (ACID)
                </button>
            </div>
        </form>
    </div>

</x-layout>
