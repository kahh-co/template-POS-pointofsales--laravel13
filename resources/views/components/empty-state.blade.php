@props(['title' => 'Belum ada data'])
<div class="py-8 text-center">
    <p class="font-semibold text-slate-700">{{ $title }}</p>
    <p class="text-sm text-slate-500">{{ $slot }}</p>
</div>
