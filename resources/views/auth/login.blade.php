<x-layout title="Login Pustakawan">

    <div class="max-w-md mx-auto my-8">
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
            {{-- Header Card --}}
            <div class="bg-gradient-to-r from-purple-900 to-purple-800 p-6 text-white text-center">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-3 text-3xl">
                    🔐
                </div>
                <h2 class="text-xl font-bold tracking-wide">Portal Pustakawan</h2>
                <p class="text-xs text-purple-200 mt-1">Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)</p>
            </div>

            {{-- Form Body --}}
            <div class="p-6 sm:p-8">
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                        @foreach($errors->all() as $err)
                            <p>⚠️ {{ $err }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Alamat Email
                        </label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', $rememberedEmail) }}"
                               placeholder="nama@del.ac.id" required autofocus
                               class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        @if($rememberedEmail)
                            <span class="text-[11px] text-purple-600 mt-1 block">ℹ️ Email terisi otomatis dari Cookie tersimpan.</span>
                        @endif
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Kata Sandi
                        </label>
                        <input type="password" id="password" name="password" required
                               placeholder="••••••••"
                               class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" name="remember" value="1"
                                   class="rounded text-purple-700 focus:ring-purple-500 border-gray-300 mr-2">
                            Ingat saya di peramban ini
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            class="w-full py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-semibold text-sm rounded-lg shadow-md transition-colors flex items-center justify-center">
                        Masuk ke Sistem
                    </button>
                </form>

                {{-- Demo Credentials Callout --}}
                <div class="mt-6 pt-5 border-t border-gray-100 bg-purple-50/60 -mx-6 -mb-6 sm:-mx-8 sm:-mb-8 p-4 text-center">
                    <p class="text-xs font-bold text-purple-900 mb-1">Akun Pustakawan Uji (Sesuai Modul):</p>
                    <p class="text-xs font-mono text-gray-700">Email: <span class="font-bold text-purple-800">pustakawan@del.ac.id</span></p>
                    <p class="text-xs font-mono text-gray-700">Password: <span class="font-bold text-purple-800">password123</span></p>
                </div>
            </div>
        </div>
    </div>

</x-layout>
