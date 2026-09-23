<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POSIFY') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    @stack('styles')
</head>
<body class="text-slate-900 antialiased min-h-screen" x-data="{ mobileOpen: false, moreOpen: false }">
<div class="flex min-h-screen">

    {{-- Sidebar desktop --}}
    <aside class="hidden lg:flex w-64 shrink-0 flex-col bg-white/90 backdrop-blur border-r border-slate-200/80 min-h-screen sticky top-0 h-screen">
        <div class="px-5 pt-6 pb-4 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-lg text-white shadow-lg shadow-indigo-600/30" style="background:linear-gradient(135deg,#4F46E5,#7C3AED)">P</div>
            <div>
                <p class="font-extrabold tracking-tight text-lg leading-none">POSIFY</p>
                <p class="text-xs text-slate-500 mt-1">Kasir modern & cepat</p>
            </div>
        </div>
        <nav class="px-3 space-y-1.5 mt-2">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                Dashboard
            </a>
            <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><path d="M4 7h16M4 7v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7M9 11h6"/></svg>
                Kasir
            </a>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*','categories.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><path d="M21 8 12 3 3 8v8l9 5 9-5V8ZM3 8l9 5 9-5M12 13v8"/></svg>
                Produk
            </a>
            <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                Transaksi
            </a>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2L10 21h4l.5-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z"/></svg>
                Settings
            </a>
        </nav>
        <div class="mt-auto p-4">
            <div class="rounded-2xl p-[1px]" style="background:linear-gradient(135deg,#C7D2FE,#DDD6FE)">
                <div class="card !border-0 !shadow-none p-3 flex items-center gap-3 !rounded-2xl">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white" style="background:linear-gradient(135deg,#4F46E5,#7C3AED)">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 font-semibold uppercase">{{ auth()->user()->role ?? 'staff' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button title="Logout" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 transition">⏻</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="flex-1 min-w-0 flex flex-col pb-24 lg:pb-0">

        {{-- Topbar --}}
        <header class="no-print sticky top-0 z-30 bg-white/75 backdrop-blur-xl border-b border-white/60 shadow-[0_8px_30px_-18px_rgb(79_70_229/.35)]">
            <div class="flex items-center gap-3 px-4 lg:px-8 py-3">
                <button class="lg:hidden w-9 h-9 rounded-xl bg-white border border-slate-200 shadow-sm" @click="mobileOpen = true">☰</button>
                <div>
                    <p class="font-extrabold tracking-tight text-lg lg:text-xl leading-none">@yield('title', 'Dashboard')</p>
                    <p class="text-xs text-slate-500 mt-1 hidden sm:block">{{ date('l, d M Y') }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <a href="{{ route('pos.index') }}" class="btn-primary !py-2 !px-4 text-sm hidden sm:inline-flex">+ Transaksi Baru</a>
                    <div class="hidden sm:flex items-center gap-2 pl-2">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold" style="background:linear-gradient(135deg,#4F46E5,#7C3AED)">{{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}</div>
                        <div class="leading-tight">
                            <p class="text-sm font-bold">{{ auth()->user()->name ?? '' }}</p>
                            <p class="text-[11px] text-slate-500 uppercase">{{ auth()->user()->role ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Mobile drawer --}}
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="mobileOpen = false"></div>
            <div class="absolute left-0 top-0 bottom-0 w-72 bg-white border-r border-slate-200 p-4 flex flex-col rounded-r-3xl"
                 x-show="mobileOpen"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-extrabold text-lg">POSIFY</p>
                    <button @click="mobileOpen = false" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500">✕</button>
                </div>
                <nav class="space-y-1.5">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">Kasir</a>
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">Produk</a>
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">Transaksi</a>
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">Settings</a>
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                    @csrf
                    <button class="w-full btn-primary !bg-none !bg-red-600 !shadow-red-600/30">Logout</button>
                </form>
            </div>
        </div>

        {{-- Content --}}
        <main class="flex-1 px-4 lg:px-8 py-5 lg:py-7 max-w-7xl w-full mx-auto">
            @if(session('success'))
                <div class="rise mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rise mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">{{ session('error') }}</div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="rise mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
                    <p class="font-bold mb-1">Ada yang perlu diperbaiki:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @hasSection('content')
                @yield('content')
            @endif
            {{ $slot ?? '' }}
        </main>

        {{-- Bottom nav mobile --}}
        <nav class="no-print lg:hidden fixed bottom-3 inset-x-3 z-40">
            <div class="bg-white/95 backdrop-blur border border-slate-200 rounded-2xl shadow-xl shadow-indigo-600/10 grid grid-cols-4 text-[11px] text-center overflow-hidden">
                <a href="{{ route('dashboard') }}" class="py-2.5 {{ request()->routeIs('dashboard') ? 'text-indigo-700 font-bold' : 'text-slate-500' }}">
                    <div class="text-lg">⌂</div>Home
                </a>
                <a href="{{ route('pos.index') }}" class="py-2.5 {{ request()->routeIs('pos.*') ? 'text-indigo-700 font-bold' : 'text-slate-500' }}">
                    <div class="text-lg">⊞</div>Kasir
                </a>
                <a href="{{ route('products.index') }}" class="py-2.5 {{ request()->routeIs('products.*') ? 'text-indigo-700 font-bold' : 'text-slate-500' }}">
                    <div class="text-lg">▤</div>Produk
                </a>
                <button @click="moreOpen = true" class="py-2.5 text-slate-500">
                    <div class="text-lg">⋯</div>Lainnya
                </button>
            </div>
        </nav>

        {{-- More sheet --}}
        <div x-show="moreOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-slate-900/50" @click="moreOpen = false"></div>
            <div class="absolute bottom-0 inset-x-0 bg-white border-t border-slate-200 rounded-t-3xl p-4 space-y-2"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
                <div class="w-10 h-1 rounded-full bg-slate-200 mx-auto mb-2"></div>
                <a href="{{ route('transactions.index') }}" class="block card p-4 font-semibold">Transaksi</a>
                <a href="{{ route('settings.index') }}" class="block card p-4 font-semibold">Settings</a>
                <button @click="moreOpen = false" class="w-full text-center text-sm text-slate-500 py-2">Tutup</button>
            </div>
        </div>

    </div>
</div>
@stack('scripts')
</body>
</html>
