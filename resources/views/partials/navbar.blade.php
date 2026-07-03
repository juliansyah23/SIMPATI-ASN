@php
    $isLoggedIn = auth()->check();
    $isAdmin    = $isLoggedIn && auth()->user()->isAdmin();
    $userName   = $isLoggedIn ? auth()->user()->name : '';
@endphp

<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-600 text-white">
                <i data-lucide="shield" class="w-5 h-5"></i>
            </span>
            <span class="leading-tight">
                <span class="block text-lg font-extrabold tracking-tight text-gray-900">SIMPATI ASN</span>
                <span class="block text-xs text-gray-500">Sistem Monitoring Psikososial ASN</span>
            </span>
        </a>

        {{-- Nav --}}
        <nav class="hidden md:flex items-center gap-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2 px-4 h-10 rounded-lg text-sm font-semibold transition
                      {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <i data-lucide="home" class="w-4 h-4"></i> Beranda
            </a>
            <a href="{{ route('kuisioner') }}"
               class="flex items-center gap-2 px-4 h-10 rounded-lg text-sm font-semibold transition
                      {{ request()->routeIs('kuisioner*') ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i> Kuisioner
            </a>
            <a href="{{ route('data') }}"
               class="flex items-center gap-2 px-4 h-10 rounded-lg text-sm font-semibold transition
                      {{ request()->routeIs('data') ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <i data-lucide="database" class="w-4 h-4"></i> Data
            </a>
            <a href="{{ route('tentang') }}"
               class="flex items-center gap-2 px-4 h-10 rounded-lg text-sm font-semibold transition
                      {{ request()->routeIs('tentang') ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <i data-lucide="info" class="w-4 h-4"></i> Tentang
            </a>
        </nav>

        {{-- Right side --}}
        @if ($isLoggedIn)
            <div class="flex items-center gap-1">
                {{-- Admin Panel (khusus admin) --}}
                @if ($isAdmin)
                <a href="{{ route('admin.index') }}"
                   class="flex items-center gap-2 px-4 h-10 rounded-lg text-sm font-semibold transition
                          {{ request()->routeIs('admin*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Panel
                </a>
                @endif

                {{-- User chip --}}
                <div class="flex items-center gap-1.5 px-3 h-10 text-sm text-gray-600">
                    <i data-lucide="user-circle-2" class="w-4 h-4"></i>
                    <span class="font-medium">{{ $userName }}</span>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-4 h-10 rounded-lg text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 transition shadow-sm">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}"
               class="flex items-center gap-2 px-5 h-10 rounded-lg text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 transition shadow-sm">
                <i data-lucide="log-in" class="w-4 h-4"></i> Login
            </a>
        @endif

    </div>
</header>
