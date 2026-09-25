<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — POSIFY</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl grid md:grid-cols-2 overflow-hidden rounded-3xl bg-white shadow-2xl shadow-indigo-600/20 border border-white/60">
        {{-- Brand panel --}}
        <div class="hero relative hidden md:flex flex-col justify-between p-8 text-white min-h-[520px]">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center font-black text-xl backdrop-blur">P</div>
                <div>
                    <p class="font-extrabold text-lg leading-none">POSIFY</p>
                    <p class="text-indigo-100 text-xs mt-1">Point of Sale modern</p>
                </div>
            </div>
            <div>
                <h2 class="text-3xl font-extrabold leading-tight">Jualan lebih cepat,<br>laporan rapi otomatis.</h2>
                <div class="flex gap-2 mt-5">
                    <div class="bg-white/15 border border-white/25 rounded-2xl px-4 py-3 backdrop-blur flex-1"><p class="font-extrabold">⚡ Cepat</p><p class="text-xs text-indigo-100">Checkout &lt; 10 detik</p></div>
                    <div class="bg-white/15 border border-white/25 rounded-2xl px-4 py-3 backdrop-blur flex-1"><p class="font-extrabold">📊 Rapi</p><p class="text-xs text-indigo-100">Grafik & struk otomatis</p></div>
                </div>
            </div>
            <p class="text-xs text-indigo-100">© {{ date('Y') }} POSIFY • Aman & responsif</p>
            <div class="absolute -right-16 -bottom-20 w-72 h-72 bg-white/10 rounded-full blur-2xl"></div>
        </div>
        {{-- Form --}}
        <div class="p-6 sm:p-10">
            <div class="md:hidden flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white" style="background:linear-gradient(135deg,#4F46E5,#7C3AED)">P</div>
                <p class="font-extrabold text-lg">POSIFY</p>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">Selamat datang 👋</h1>
            <p class="text-sm text-slate-500 mt-1 mb-6">Masuk untuk buka kasir & dashboard.</p>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-slate-700">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus class="input mt-1.5 !py-3" placeholder="admin" autocomplete="username">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Password</label>
                    <input type="password" name="password" required class="input mt-1.5 !py-3" placeholder="••••••••" autocomplete="current-password">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="w-4 h-4 accent-indigo-600"> Ingat saya
                </label>
                <button type="submit" class="btn-primary w-full !py-3.5 text-base">Masuk ke Dashboard →</button>
            </form>

            <div class="mt-5 rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100 p-4 text-xs text-slate-600">
                <p class="font-bold text-indigo-900 mb-1">Akun demo</p>
                <div class="space-y-2 font-mono">
                    <div class="flex gap-2">
                        <code class="flex-1 bg-white border border-indigo-100 rounded-lg px-3 py-2">admin</code>
                        <code class="flex-1 bg-white border border-indigo-100 rounded-lg px-3 py-2">admin321</code>
                    </div>
                    <div class="flex gap-2">
                        <code class="flex-1 bg-white border border-indigo-100 rounded-lg px-3 py-2">owner@posify.id</code>
                        <code class="flex-1 bg-white border border-indigo-100 rounded-lg px-3 py-2">password</code>
                    </div>
                    <div class="flex gap-2">
                        <code class="flex-1 bg-white border border-indigo-100 rounded-lg px-3 py-2">kasir@posify.id</code>
                        <code class="flex-1 bg-white border border-indigo-100 rounded-lg px-3 py-2">password</code>
                    </div>
                </div>
                <p class="mt-2 text-slate-500">Bisa login pakai username <span class="font-mono">admin</span> atau email. Bukan <span class="font-mono">admin123</span>.</p>
            </div>
        </div>
    </div>
</body>
</html>
