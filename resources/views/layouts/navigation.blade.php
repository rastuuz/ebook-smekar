<nav x-data="{ open: false }" @keydown.escape.window="open = false" @resize.window="if (window.innerWidth >= 640) open = false" class="sticky top-0 z-30 border-b border-white/10 bg-gradient-to-b from-[#101f42] to-[#0c1730]/95 text-white shadow-lux backdrop-blur">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                            <x-school-logo size="sm" />
                            <span class="leading-tight">
                                <span class="block font-display text-base font-semibold tracking-wide text-white">E-Library</span>
                                <span class="block text-[10px] font-medium uppercase tracking-[0.2em] text-[#d9c27a]">SMKN 1 Dukuhturi</span>
                            </span>
                        </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    @if(auth()->user()?->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Kelola') }}
                        </x-nav-link>
                        <x-nav-link :href="route('ebooks.index')" :active="request()->routeIs('ebooks.*')">
                            {{ __('Katalog') }}
                        </x-nav-link>
                        <x-nav-link :href="route('videos.index')" :active="request()->routeIs('videos.*')">
                            {{ __('Video') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('ebooks.index')" :active="request()->routeIs('ebooks.*')">
                            {{ __('Katalog') }}
                        </x-nav-link>
                        <x-nav-link :href="route('bookmarks.index')" :active="request()->routeIs('bookmarks.*')">
                            {{ __('Simpanan') }}
                        </x-nav-link>
                        @if(auth()->user()?->isGuru())
                            <x-nav-link :href="route('videos.index')" :active="request()->routeIs('videos.*')">
                                {{ __('Video') }}
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2.5 rounded-xl border border-white/15 bg-white/[0.06] py-1.5 pl-1.5 pr-3.5 text-sm font-medium text-white shadow-sm transition duration-200 hover:border-[#c9a227]/40 hover:bg-white/10 focus:outline-none">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-b from-[#e3c964] to-[#a8861d] text-sm font-bold text-[#1a1405] shadow-sm ring-1 ring-inset ring-white/40">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="max-w-[140px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="h-3.5 w-3.5 text-stone-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4Z" clip-rule="evenodd"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg border border-white/15 bg-white/5 px-3.5 py-2 text-stone-200 transition hover:bg-white/10 focus:outline-none">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.2em]" x-text="open ? 'Tutup' : 'Menu'">Menu</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-show="open" style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-white/10 bg-[#0e1b33] sm:hidden">
        <div class="space-y-1 px-4 py-3">
            @if(auth()->user()?->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    {{ __('Kelola') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('ebooks.index')" :active="request()->routeIs('ebooks.*')">
                    {{ __('Katalog') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('videos.index')" :active="request()->routeIs('videos.*')">
                    {{ __('Video') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('ebooks.index')" :active="request()->routeIs('ebooks.*')">
                    {{ __('Katalog') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bookmarks.index')" :active="request()->routeIs('bookmarks.*')">
                    {{ __('Simpanan') }}
                </x-responsive-nav-link>
                @if(auth()->user()?->isGuru())
                    <x-responsive-nav-link :href="route('videos.index')" :active="request()->routeIs('videos.*')">
                        {{ __('Video') }}
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-white/10 px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#c9a227] text-base font-bold text-[#1a1405]">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <div class="truncate font-semibold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-stone-400">{{ Auth::user()->isSiswa() ? ('NIS '.Auth::user()->nis) : ('@'.Auth::user()->username) }}</div>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-lg border border-white/15 px-3 py-2 text-xs font-medium text-white">{{ __('Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg border border-white/15 px-3 py-2 text-xs font-medium text-white">{{ __('Log Out') }}</button>
                </form>
            </div>
        </div>
    </div>
</nav>
