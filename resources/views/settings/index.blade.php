@extends('layouts.app')
@section('title', 'Settings')
@section('content')
@php $s = $settings ?? $setting ?? null; @endphp
<div x-data="{ tab: '{{ session('settings_tab', 'store') }}' }">
    <h1 class="text-2xl font-extrabold tracking-tight mb-1">Settings</h1>
    <p class="text-sm text-slate-500 mb-5">Kelola toko, struk, pajak, dan akun.</p>

    <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
        <template x-for="t in [['store','Store'],['receipt','Receipt'],['tax','Tax'],['account','Account']]" :key="t[0]">
            <button @click="tab = t[0]" :class="tab===t[0] ? 'pill active border border-transparent' : 'pill bg-white border border-slate-200 text-slate-600'" class="shrink-0 px-4 py-2 rounded-full text-sm font-bold" x-text="t[1]"></button>
        </template>
    </div>

    {{-- STORE --}}
    <div x-show="tab==='store'" class="card p-6 max-w-2xl">
        <h2 class="font-extrabold mb-1">Profil Toko</h2>
        <p class="text-xs text-slate-500 mb-4">Nama dan alamat tampil di struk.</p>
        <form method="POST" action="{{ route('settings.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="text-sm font-semibold text-slate-600">Nama Toko</label><input name="store_name" value="{{ old('store_name', $s->store_name ?? '') }}" required class="input mt-1"></div>
            <div><label class="text-sm font-semibold text-slate-600">Alamat</label><textarea name="address" class="input mt-1" rows="2">{{ old('address', $s->address ?? '') }}</textarea></div>
            <div><label class="text-sm font-semibold text-slate-600">Telepon</label><input name="phone" value="{{ old('phone', $s->phone ?? '') }}" class="input mt-1"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-600">Logo</label>
                    @if(!empty($s->logo))<img src="{{ \Illuminate\Support\Facades\Storage::url($s->logo) }}" class="w-16 h-16 rounded-xl object-cover my-2 border border-slate-200" alt="logo">@endif
                    <input type="file" name="logo" accept="image/*" class="input mt-1">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-600">Gambar QRIS</label>
                    @if(!empty($s->qris_image))<img src="{{ \Illuminate\Support\Facades\Storage::url($s->qris_image) }}" class="w-16 h-16 rounded-xl object-cover my-2 border border-slate-200" alt="qris">@endif
                    <input type="file" name="qris_image" accept="image/*" class="input mt-1">
                </div>
            </div>
            <button class="btn-primary">Simpan Toko</button>
        </form>
    </div>

    {{-- RECEIPT --}}
    <div x-show="tab==='receipt'" x-cloak class="grid lg:grid-cols-2 gap-4 max-w-4xl">
        <div class="card p-6">
            <h2 class="font-extrabold mb-1">Pengaturan Struk</h2>
            <p class="text-xs text-slate-500 mb-4">Ukuran kertas & catatan kaki.</p>
            <form method="POST" action="{{ route('settings.receipt') }}" class="space-y-4">
                @csrf @method('PUT')
                <div><label class="text-sm font-semibold text-slate-600">Ukuran Kertas</label>
                    <select name="paper_size" class="input mt-1">
                        <option value="58mm" @selected(old('paper_size', $s->paper_size ?? '80mm')=='58mm')>58mm</option>
                        <option value="80mm" @selected(old('paper_size', $s->paper_size ?? '80mm')=='80mm')>80mm</option>
                    </select>
                </div>
                <div><label class="text-sm font-semibold text-slate-600">Catatan Kaki</label><textarea name="receipt_footer" class="input mt-1" rows="3">{{ old('receipt_footer', $s->receipt_footer ?? '') }}</textarea></div>
                <button class="btn-primary">Simpan Struk</button>
            </form>
        </div>
        <div class="card p-6">
            <h2 class="font-extrabold mb-4">Preview Struk</h2>
            <div class="bg-white text-black font-mono text-xs rounded-xl p-4 w-56 mx-auto text-center border border-dashed border-slate-300 shadow-sm">
                <p class="font-bold">{{ $s->store_name ?? 'POSIFY Store' }}</p>
                <p>{{ $s->address ?? 'Jl. Contoh No. 1' }}</p>
                <p class="border-t border-dashed border-slate-400 my-2"></p>
                <p class="text-left">Kopi Susu × 2 .... 30.000</p>
                <p class="text-left">Roti Bakar × 1 .... 12.000</p>
                <p class="border-t border-dashed border-slate-400 my-2"></p>
                <p class="flex justify-between font-bold"><span>TOTAL</span><span>42.000</span></p>
                <p class="mt-2">{{ $s->receipt_footer ?? 'Terima kasih!' }}</p>
            </div>
        </div>
    </div>

    {{-- TAX --}}
    <div x-show="tab==='tax'" x-cloak class="card p-6 max-w-2xl">
        <h2 class="font-extrabold mb-1">Pajak</h2>
        <p class="text-xs text-slate-500 mb-4">PB1 / PPN ditambahkan otomatis di kasir.</p>
        <form method="POST" action="{{ route('settings.tax') }}" class="space-y-4">
            @csrf @method('PUT')
            <label class="flex items-center gap-3 text-sm font-medium">
                <input type="checkbox" name="tax_enabled" value="1" @checked(old('tax_enabled', !empty($s->tax_enabled))) class="w-5 h-5 accent-indigo-600">
                Aktifkan pajak
            </label>
            <div><label class="text-sm font-semibold text-slate-600">Persen Pajak (%)</label><input type="number" min="0" max="100" step="0.1" name="tax_percent" value="{{ old('tax_percent', $s->tax_percent ?? 0) }}" class="input mt-1"></div>
            <button class="btn-primary">Simpan Pajak</button>
        </form>
    </div>

    {{-- ACCOUNT --}}
    <div x-show="tab==='account'" x-cloak class="card p-6 max-w-2xl">
        <h2 class="font-extrabold mb-1">Akun Saya</h2>
        <p class="text-xs text-slate-500 mb-4">Login memakai <b>username</b> (nama), bukan email.</p>
        <form method="POST" action="{{ route('settings.account') }}" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="text-sm font-semibold text-slate-600">Username</label><input name="name" value="{{ old('name', auth()->user()->name) }}" required class="input mt-1"></div>
            <div><label class="text-sm font-semibold text-slate-600">Email</label><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="input mt-1"></div>
            <div><label class="text-sm font-semibold text-slate-600">Password Baru <span class="font-normal text-slate-400">(kosongkan jika tidak diubah, min. 8 karakter)</span></label><input type="password" name="password" class="input mt-1" autocomplete="new-password"></div>
            <div class="flex flex-col sm:flex-row gap-2">
                <button class="btn-primary flex-1">Update Akun</button>
            </div>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="w-full px-4 py-2.5 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm font-bold hover:bg-red-100 transition">Logout</button>
        </form>
    </div>
</div>
@endsection
