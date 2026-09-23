@props(['label', 'value', 'sub' => null, 'icon' => '📊', 'color' => 'indigo'])
@php
$colors = [
  'indigo' => 'bg-indigo-100',
  'emerald' => 'bg-emerald-100',
  'amber' => 'bg-amber-100',
  'rose' => 'bg-rose-100',
];
@endphp
<div class="card card-hover p-4">
    <div class="stat-icon {{ $colors[$color] ?? $colors['indigo'] }}">{{ $icon }}</div>
    <p class="text-xs text-slate-500 mt-3 font-medium">{{ $label }}</p>
    <p class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ $value }}</p>
    @if($sub)
        <p class="text-xs text-slate-500 mt-1">{{ $sub }}</p>
    @endif
</div>
