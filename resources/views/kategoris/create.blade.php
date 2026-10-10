<x-layout title="Tambah Kategori">

    <div class="mb-6">
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('kategoris.index') }}" class="hover:text-purple-700">Daftar Kategori</a>
            <span class="mx-2">›</span>
            <span class="text-gray-900 font-medium">Tambah Kategori Baru</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">➕ Tambah Kategori Baru</h1>
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

        <form action="{{ route('kategoris.store') }}" method="POST" novalidate>
            @csrf

            <div class="space-y-6">
                {{-- Kode Kategori --}}
                <div>
                    <label for="kode_kategori" class="block text-sm font-medium text-gray-700 mb-1">
                        Kode Kategori <span class="text-red-500">*</span>
                        <span class="text-gray-400 text-xs">(Maks 10 karakter, unik, tanpa spasi)</span>
                    </label>
                    <input type="text" id="kode_kategori" name="kode_kategori" value="{{ old('kode_kategori') }}"
                           placeholder="Contoh: KAT-FIK"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('kode_kategori') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('kode_kategori')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Kategori --}}
                <div>
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}"
                           placeholder="Contoh: Fiksi"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                  {{ $errors->has('nama_kategori') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('nama_kategori')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('kategoris.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg shadow transition-colors">
                    💾 Simpan Kategori
                </button>
            </div>
        </form>
    </div>

</x-layout>
