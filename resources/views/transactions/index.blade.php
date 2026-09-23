@extends('layouts.app')
@section('title', 'Transaksi')
@section('content')
<div x-data="{ drawer: false, detail: null, loading: false }">
    <h1 class="text-2xl font-extrabold mb-1">Transaksi</h1>
    <p class="text-sm text-slate-500 mb-5">Riwayat penjualan — klik baris untuk detail</p>

    <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-col sm:flex-row gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari invoice / kasir..." class="input sm:max-w-xs">
        <select name="filter" class="input sm:w-48" onchange="this.form.submit()">
            <option value="today" @selected(request('filter')=='today')>Hari ini</option>
            <option value="yesterday" @selected(request('filter')=='yesterday')>Kemarin</option>
            <option value="7days" @selected(request('filter')=='7days')>7 hari terakhir</option>
            <option value="all" @selected(request('filter')=='all')>Semua</option>
        </select>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="text-left text-xs text-slate-500 uppercase border-b border-slate-200">
                    <tr><th class="px-4 py-3">ID</th><th class="px-4 py-3">Cashier</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3">Time</th></tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $trx)
                    <tr class="border-b border-slate-200 last:border-0 hover:bg-slate-50 cursor-pointer"
                        @click="loading=true; drawer=true; detail=null; fetch('/transactions/{{ $trx->id }}', {headers:{'Accept':'application/json'}}).then(r=>r.json()).then(d=>{detail=d.transaction||d; loading=false}).catch(()=>loading=false)">
                        <td class="px-4 py-3 font-mono font-semibold">{{ $trx->invoice_no ?? $trx->id }}</td>
                        <td class="px-4 py-3">{{ $trx->user->name ?? $trx->cashier ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold whitespace-nowrap">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3"><x-badge :type="strtolower($trx->payment_method ?? '')==='cash' ? 'success' : 'primary'">{{ $trx->payment_method }}</x-badge></td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $trx->created_at?->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada transaksi" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Side drawer --}}
    <div x-show="drawer" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/40" @click="drawer = false"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white border-l border-slate-200 p-5 overflow-y-auto"
             x-show="drawer" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Detail Transaksi</h2>
                <button @click="drawer=false" class="text-xl text-slate-500">✕</button>
            </div>
            <div x-show="loading" class="text-sm text-slate-500 py-8 text-center">Memuat...</div>
            <template x-if="detail">
                <div>
                    <p class="font-mono font-bold" x-text="detail.invoice_no || detail.id"></p>
                    <p class="text-xs text-slate-500" x-text="detail.created_at"></p>
                    <div class="my-4 space-y-2 text-sm border-y border-slate-200 py-3">
                        <template x-for="it in (detail.items || [])" :key="it.id">
                            <div class="flex justify-between gap-2">
                                <span><span x-text="it.name"></span> <span class="text-slate-500">×</span> <span x-text="it.qty ?? it.quantity"></span></span>
                                <span class="font-semibold" x-text="'Rp ' + Number(it.subtotal||0).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-between font-extrabold text-lg"><span>Total</span><span x-text="'Rp ' + Number(detail.total||0).toLocaleString('id-ID')"></span></div>
                    <a :href="'/transactions/' + detail.id + '/receipt'" class="btn-primary w-full mt-4 block text-center">🖨 Print Receipt</a>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
