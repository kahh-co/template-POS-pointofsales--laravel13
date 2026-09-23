@props(['type' => 'muted'])
@php
$map = [
  'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
  'danger'  => 'bg-red-50 text-red-700 border-red-200',
  'primary' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
  'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
  'muted'   => 'bg-slate-100 text-slate-600 border-slate-200',
];
@endphp
<span {{ $attributes->merge(['class' => 'text-[11px] px-2.5 py-1 rounded-full border font-bold uppercase tracking-wide ' . ($map[$type] ?? $map['muted'])]) }}>{{ $slot }}</span>
