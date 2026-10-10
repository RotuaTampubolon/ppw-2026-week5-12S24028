<x-layout title="Tambah Buku">

    <div class="mb-6">
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('bukus.index') }}" class="hover:text-purple-700">Daftar Buku</a>
            <span class="mx-2">›</span>
            <span class="text-gray-900 font-medium">Tambah Buku Baru</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">➕ Tambah Buku Baru</h1>
    </div>

    <div class="max-w-3xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        {{-- Error Validation Summary --}}
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

        <form action="{{ route('bukus.store') }}" method="POST" novalidate>
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- ISBN --}}
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700 mb-1">
                        ISBN <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="isbn" name="isbn" value="{{ old('isbn') }}"
                           placeholder="Contoh: 9780062315007"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('isbn') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('isbn')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tahun Terbit --}}
                <div>
                    <label for="tahun_terbit" class="block text-sm font-medium text-gray-700 mb-1">
                        Tahun Terbit <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit') }}"
                           min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('tahun_terbit') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('tahun_terbit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Judul --}}
                <div class="md:col-span-2">
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">
                        Judul Buku <span class="text-red-500">*</span>
                        <span class="text-gray-400 text-xs">(minimal 5 karakter)</span>
                    </label>
                    <input type="text" id="judul" name="judul" value="{{ old('judul') }}"
                           placeholder="Judul buku (minimal 5 karakter)"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('judul') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('judul')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Penulis --}}
                <div>
                    <label for="penulis" class="block text-sm font-medium text-gray-700 mb-1">
                        Penulis <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="penulis" name="penulis" value="{{ old('penulis') }}"
                           placeholder="Nama penulis"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('penulis') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('penulis')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Penerbit --}}
                <div>
                    <label for="penerbit" class="block text-sm font-medium text-gray-700 mb-1">
                        Penerbit <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="penerbit" name="penerbit" value="{{ old('penerbit') }}"
                           placeholder="Nama penerbit"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('penerbit') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('penerbit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select id="kategori_id" name="kategori_id"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('kategori_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->kode_kategori }} — {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Stok --}}
                <div>
                    <label for="stok" class="block text-sm font-medium text-gray-700 mb-1">
                        Stok <span class="text-red-500">*</span>
                        <span class="text-gray-400 text-xs">(min. 0)</span>
                    </label>
                    <input type="number" id="stok" name="stok" value="{{ old('stok', 0) }}"
                           min="0" placeholder="0"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('stok') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('stok')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sinopsis --}}
                <div class="md:col-span-2">
                    <label for="sinopsis" class="block text-sm font-medium text-gray-700 mb-1">
                        Sinopsis <span class="text-gray-400 text-xs">(opsional)</span>
                    </label>
                    <textarea id="sinopsis" name="sinopsis" rows="4"
                              placeholder="Deskripsi singkat tentang buku..."
                              class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                     {{ $errors->has('sinopsis') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">{{ old('sinopsis') }}</textarea>
                    @error('sinopsis')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('bukus.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg shadow transition-colors">
                    💾 Simpan Buku
                </button>
            </div>
        </form>
    </div>

</x-layout>
