<x-guest-layout>
    <div class="mb-6 text-center">
        <span class="badge-gold shadow-sm">Siswa</span>
        <h1 class="title-display mt-3 text-[1.7rem] leading-tight">SMKN 1 Dukuhturi</h1>
        <p class="mt-1 text-sm text-stone-500">Perpustakaan digital — masuk dengan NIS</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login.nis.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="nis" value="NIS (Nomor Induk Siswa)" />
            <x-text-input id="nis" name="nis" type="text" inputmode="numeric"
                class="mt-1 block w-full py-2.5 text-center text-lg font-bold tracking-[0.3em]" :value="old('nis')"
                required autofocus autocomplete="username"
                placeholder="18034" />
            <x-input-error :messages="$errors->get('nis')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input id="remember" type="checkbox" name="remember"
                class="rounded border-stone-300 text-brand-700 shadow-sm focus:ring-brand-600">
            <label for="remember" class="text-sm text-stone-600">Ingat saya</label>
        </div>

        <x-primary-button>
            Masuk sebagai Siswa
        </x-primary-button>
    </form>

    <div class="mt-6 space-y-2 border-t border-stone-200/80 bg-stone-50/60 -mx-6 -mb-6 sm:-mx-8 sm:-mb-8 px-6 py-4 text-center text-sm rounded-b-2xl">
        <p>
            <span class="text-stone-500">Anda guru?</span>
            <a href="{{ route('login.guru') }}" class="font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">
                Masuk sebagai Guru →
            </a>
        </p>
        <p>
            <span class="text-stone-500">Anda admin?</span>
            <a href="{{ route('login.admin') }}" class="font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">
                Masuk sebagai Admin →
            </a>
        </p>
    </div>
</x-guest-layout>
