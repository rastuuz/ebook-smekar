<x-guest-layout>
    <div class="mb-6 text-center">
        <span class="badge-cat shadow-sm">Guru</span>
        <h1 class="title-display mt-3 text-[1.7rem] leading-tight">Masuk sebagai Guru</h1>
        <p class="mt-1 text-sm text-stone-500">Masukkan NIP dan password.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login.guru.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="username" value="NIP guru" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username"
                :value="old('username')" required autofocus autocomplete="username"
                placeholder="Contoh: 198503122009011001" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password"
                name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                class="rounded border-stone-300 text-brand-700 shadow-sm focus:ring-brand-600">
            <label for="remember_me" class="text-sm text-stone-600">Ingat saya</label>
        </div>

        <x-primary-button>
            Masuk sebagai Guru
        </x-primary-button>
    </form>

    <div class="mt-6 space-y-2 border-t border-stone-200/80 bg-stone-50/60 -mx-6 -mb-6 sm:-mx-8 sm:-mb-8 px-6 py-4 text-center text-sm rounded-b-2xl">
        <p>
            <span class="text-stone-500">Anda admin?</span>
            <a href="{{ route('login.admin') }}" class="font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">Masuk sebagai Admin →</a>
        </p>
        <p>
            <span class="text-stone-500">Anda siswa?</span>
            <a href="{{ route('login.nis') }}" class="font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">Masuk dengan NIS →</a>
        </p>
    </div>
</x-guest-layout>
