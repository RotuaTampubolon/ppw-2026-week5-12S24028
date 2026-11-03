<x-layout title="Login Pustakawan">
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="w-full max-w-md">

            {{-- Header Card --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-900 rounded-full mb-4">
                    <svg class="h-8 w-8 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Login Pustakawan</h1>
                <p class="text-gray-500 text-sm mt-1">Masuk ke sistem SIPUS-Del</p>
            </div>

            {{-- Cookie Info --}}
            @if(isset($_COOKIE['del_last_user_email']))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                    🍪 Terdeteksi sesi sebelumnya untuk:
                    <strong>{{ htmlspecialchars($_COOKIE['del_last_user_email']) }}</strong>
                </div>
            @endif

            {{-- Login Form --}}
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form action="{{ route('login.post') }}" method="POST" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Alamat Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', isset($_COOKIE['del_last_user_email']) ? $_COOKIE['del_last_user_email'] : '') }}"
                            placeholder="pustakawan@del.ac.id"
                            autofocus
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus:border-purple-500' }}"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-purple-500
                                   {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus:border-purple-500' }}"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center mb-6">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                            class="h-4 w-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500"
                        >
                        <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-purple-900 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                    >
                        🔑 Masuk ke SIPUS-Del
                    </button>
                </form>

                {{-- Hint --}}
                <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400">Akun pustakawan demo:</p>
                    <p class="text-xs text-gray-600 font-mono mt-1">
                        pustakawan@del.ac.id / password123
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-layout>
