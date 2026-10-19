<x-app-layout>
    <x-slot name="title">Profil</x-slot>
    <x-slot name="header">
        <p class="eyebrow">Akun</p>
        <h2 class="title-display mt-2 text-2xl">Profil</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div><x-back-button :href="route('dashboard')" label="Dashboard" /></div>
            <div class="card card-pad">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            @if(! empty($user->password))
                <div class="card card-pad">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="card card-pad">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @else
                <div class="card card-pad">
                    <div class="max-w-xl text-sm text-stone-600">
                        <h2 class="title-display text-lg">Akun Siswa (NIS)</h2>
                        <p class="mt-1 leading-relaxed">Anda login tanpa password. Ganti password dan hapus akun tidak tersedia. Hubungi admin untuk perubahan data NIS/kelas.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
