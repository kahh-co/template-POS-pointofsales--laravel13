@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
  $hour = (int) date('H');
  $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 18 ? 'Selamat siang' : 'Selamat malam');
  $defaultLabels = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
  $labels = isset($chartLabels) && count($chartLabels) ? $chartLabels : $defaultLabels;
  $values = isset($chartData) && count($chartData) ? $chartData : [0,0,0,0,0,0,0];
  $rev = $todayRevenue ?? $revenueToday ?? 0;
  $trxCount = $todayCount ?? $transactionCount ?? 0;
  $items = $todayItems ?? $itemsSold ?? 0;
@endphp

{{-- Hero --}}
<div class="hero rise relative overflow-hidden rounded-3xl text-white p-5 sm:p-7 mb-5 shadow-xl shadow-indigo-600/25">
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
            <p class="text-indigo-100 text-sm">{{ date('l, d M Y') }}</p>
            <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight mt-1">{{ $greet }}, {{ auth()->user()->name ?? 'Admin' }} 👋</h1>
            <p class="text-indigo-100/90 text-sm mt-1">Hari ini ada <b class="text-white">{{ $trxCount }} transaksi</b> dengan omzet <b class="text-white">Rp {{ number_format($rev,0,',','.') }}</b></p>
            <div class="flex gap-2 mt-4">
                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 bg-white text-indigo-700 font-bold text-sm px-4 py-2.5 rounded-xl shadow hover:-translate-y-0.5 transition">Buka Kasir →</a>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-white/15 border border-white/25 text-white font-semibold text-sm px-4 py-2.5 rounded-xl backdrop-blur hover:bg-white/25 transition">Kelola Produk</a>
            </div>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-1 lg:grid-cols-3 xl:grid-cols-3 gap-2 sm:min-w-[320px]">
            <div class="bg-white/12 border border-white/20 rounded-2xl p-3 backdrop-blur text-center">
                <p class="text-[11px] text-indigo-100 uppercase font-semibold">Omzet</p>
                <p class="font-extrabold text-sm sm:text-base">Rp {{ number_format($rev,0,',','.') }}</p>
            </div>
            <div class="bg-white/12 border border-white/20 rounded-2xl p-3 backdrop-blur text-center">
                <p class="text-[11px] text-indigo-100 uppercase font-semibold">Transaksi</p>
                <p class="font-extrabold text-sm sm:text-base">{{ $trxCount }}</p>
            </div>
            <div class="bg-white/12 border border-white/20 rounded-2xl p-3 backdrop-blur text-center">
                <p class="text-[11px] text-indigo-100 uppercase font-semibold">Item</p>
                <p class="font-extrabold text-sm sm:text-base">{{ $items }}</p>
            </div>
        </div>
    </div>
    <div class="absolute -right-10 -bottom-16 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
    <div class="absolute right-24 -top-14 w-40 h-40 rounded-full bg-violet-300/30 blur-2xl"></div>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="card card-hover rise rise-1 p-4">
        <div class="flex items-center justify-between">
            <div class="stat-icon bg-emerald-100">💰</div>
            <span class="text-[11px] font-bold px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $trxCount }} trx</span>
        </div>
        <p class="text-xs text-slate-500 mt-3 font-medium">Revenue Hari Ini</p>
        <p class="text-lg sm:text-2xl font-extrabold tracking-tight">Rp {{ number_format($rev,0,',','.') }}</p>
    </div>
    <div class="card card-hover rise rise-2 p-4">
        <div class="flex items-center justify-between">
            <div class="stat-icon bg-indigo-100">🧾</div>
            <span class="text-[11px] font-bold px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">Hari ini</span>
        </div>
        <p class="text-xs text-slate-500 mt-3 font-medium">Transaksi</p>
        <p class="text-lg sm:text-2xl font-extrabold tracking-tight">{{ $trxCount }}</p>
    </div>
    <div class="card card-hover rise rise-3 p-4">
        <div class="flex items-center justify-between">
            <div class="stat-icon bg-amber-100">📦</div>
            <span class="text-[11px] font-bold px-2 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">Pcs</span>
        </div>
        <p class="text-xs text-slate-500 mt-3 font-medium">Items Terjual</p>
        <p class="text-lg sm:text-2xl font-extrabold tracking-tight">{{ $items }}</p>
    </div>
    <div class="card card-hover rise rise-4 p-4 {{ ($lowStockCount ?? 0) > 0 ? '!border-red-200' : '' }}">
        <div class="flex items-center justify-between">
            <div class="stat-icon bg-rose-100">⚠️</div>
            <span class="text-[11px] font-bold px-2 py-1 rounded-full {{ ($lowStockCount ?? 0) > 0 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">Restock</span>
        </div>
        <p class="text-xs text-slate-500 mt-3 font-medium">Low Stock</p>
        <p class="text-lg sm:text-2xl font-extrabold tracking-tight {{ ($lowStockCount ?? 0) > 0 ? 'text-red-600' : '' }}">{{ $lowStockCount ?? 0 }}</p>
    </div>
</div>

<div class="card rise p-4 sm:p-5 mb-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="font-extrabold tracking-tight">Grafik Penjualan</h2>
            <p class="text-xs text-slate-500">Omzet 7 hari terakhir</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 border border-violet-100">Live</span>
    </div>
    <div class="h-60 sm:h-72"><canvas id="salesChart"></canvas></div>
</div>

<div class="grid lg:grid-cols-5 gap-4">
    <div class="card rise p-4 sm:p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-extrabold tracking-tight">Produk Terlaris</h2>
            <span class="text-xs text-slate-400">Top 5</span>
        </div>
        @forelse($bestSelling ?? $bestSellers ?? [] as $i => $row)
            <div class="flex items-center gap-3 py-3 border-b border-slate-100 last:border-0">
                <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black {{ $i===0 ? 'text-white shadow' : 'bg-slate-100 text-slate-600' }}" @if($i===0) style="background:linear-gradient(135deg,#F59E0B,#EF4444)" @endif>{{ $i + 1 }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm truncate">{{ is_array($row) ? ($row['name'] ?? '-') : ($row->product->name ?? $row->name ?? '-') }}</p>
                    <div class="h-1.5 rounded-full bg-slate-100 mt-1.5 overflow-hidden">
                        @php $sold = is_array($row) ? ($row['sold'] ?? 0) : ($row->total_qty ?? 0); @endphp
                        <div class="h-full rounded-full" style="width:{{ min(100, $sold*12) }}%;background:linear-gradient(90deg,#4F46E5,#7C3AED)"></div>
                    </div>
                </div>
                <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-full whitespace-nowrap">{{ $sold }} terjual</span>
            </div>
        @empty
            <x-empty-state title="Belum ada penjualan" />
        @endforelse
    </div>
    <div class="card rise p-4 sm:p-5 lg:col-span-3">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-extrabold tracking-tight">Transaksi Terakhir</h2>
            <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Lihat semua →</a>
        </div>
        @forelse($recentTransactions ?? [] as $trx)
            <div class="flex items-center gap-3 py-3 border-b border-slate-100 last:border-0 text-sm hover:bg-indigo-50/40 rounded-xl px-2 -mx-2 transition">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-100 to-violet-100 border border-indigo-100 flex items-center justify-center font-black text-indigo-700">₨</div>
                <div class="flex-1 min-w-0">
                    <p class="font-mono font-bold truncate">{{ $trx->invoice_no ?? $trx->id }}</p>
                    <p class="text-xs text-slate-500">{{ $trx->created_at?->format('d M H:i') }} • {{ $trx->user->name ?? 'Kasir' }}</p>
                </div>
                <x-badge :type="strtolower($trx->payment_method ?? '') === 'cash' ? 'success' : 'primary'">{{ $trx->payment_method }}</x-badge>
                <p class="font-extrabold whitespace-nowrap">Rp {{ number_format($trx->total ?? 0, 0, ',', '.') }}</p>
            </div>
        @empty
            <x-empty-state title="Belum ada transaksi" />
        @endforelse
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('salesChart');
  if (!el || !window.Chart) return;
  const ctx = el.getContext('2d');
  const g = ctx.createLinearGradient(0, 0, 0, 260);
  g.addColorStop(0, 'rgba(99,102,241,.35)');
  g.addColorStop(1, 'rgba(99,102,241,0)');
  new Chart(el, {
    type: 'line',
    data: {
      labels: @json($labels),
      datasets: [{
        label: 'Revenue',
        data: @json($values),
        borderColor: '#4F46E5',
        backgroundColor: g,
        fill: true,
        tension: .45,
        pointRadius: 4,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#4F46E5',
        pointBorderWidth: 2,
        borderWidth: 3
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' Rp ' + Number(c.raw||0).toLocaleString('id-ID') } } },
      scales: {
        x: { ticks: { color: '#64748B', font: { weight: 600 } }, grid: { color: '#F1F5F9' } },
        y: { ticks: { color: '#64748B', callback: v => v >= 1000 ? (v/1000)+'k' : v }, grid: { color: '#F1F5F9' }, border: { display: false } }
      }
    }
  });
});
</script>
@endpush
@endsection
