<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIPUS-Del' }} — Perpustakaan Kampus Del</title>

    {{-- Vite: Tailwind CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Del Purple Theme: #4C1D95 */
        :root {
            --del-purple: #4C1D95;
            --del-purple-light: #6D28D9;
            --del-purple-dark: #3B0764;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Navigation Bar --}}
    <nav class="bg-gradient-to-r from-purple-900 to-purple-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('bukus.index') }}" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-white font-bold text-xl tracking-wide">SIPUS-Del</span>
                    </a>
                    <span class="text-purple-300 text-sm hidden md:block">Sistem Informasi Perpustakaan</span>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('bukus.index') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                              {{ request()->routeIs('bukus.*') ? 'bg-white text-purple-900' : 'text-white hover:bg-purple-600' }}">
                        📚 Daftar Buku
                    </a>
                    <a href="{{ route('kategoris.index') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                              {{ request()->routeIs('kategoris.*') ? 'bg-white text-purple-900' : 'text-white hover:bg-purple-600' }}">
                        🗂️ Kategori
                    </a>
                    <a href="{{ route('bukus.create') }}"
                       class="ml-2 px-4 py-2 rounded-md text-sm font-medium bg-yellow-400 text-purple-900 hover:bg-yellow-300 font-semibold transition-colors">
                        + Tambah Buku
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="flex items-center p-4 mb-4 bg-green-50 border border-green-300 text-green-800 rounded-lg shadow-sm" role="alert">
                <svg class="w-5 h-5 mr-3 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center p-4 mb-4 bg-red-50 border border-red-300 text-red-800 rounded-lg shadow-sm" role="alert">
                <svg class="w-5 h-5 mr-3 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-red-600 hover:text-red-800">✕</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="flex items-center p-4 mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg shadow-sm" role="alert">
                <svg class="w-5 h-5 mr-3 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('warning') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-yellow-600 hover:text-yellow-800">✕</button>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-purple-900 text-purple-300 text-center text-sm py-4 mt-12">
        <p>© {{ date('Y') }} <strong class="text-white">SIPUS-Del</strong> — Institut Teknologi Del · Pemrograman & Pengujian Web</p>
    </footer>

</body>
</html>
