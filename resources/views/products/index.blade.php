@extends('layouts.app')
@section('title', 'Produk')
@section('content')
<div x-data="{ addOpen: false, editId: null, catOpen: false }">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight">Produk</h1>
            <p class="text-sm text-slate-500">{{ ($products->total() ?? count($products ?? [])) }} produk terdaftar</p>
        </div>
        <div class="sm:ml-auto flex gap-2">
            <button @click="catOpen = true" class="btn-soft text-sm">+ Kategori</button>
            <button @click="addOpen = true" class="btn-primary text-sm">+ Tambah Produk</button>
        </div>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="input sm:max-w-xs">
        <select name="category" class="input sm:w-48" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->name }}" @selected(request('category')==$cat->name)>{{ $cat->name }}</option>
            @endforeach
        </select>
        @if(request('search') || request('category'))
            <a href="{{ route('products.index') }}" class="btn-soft text-sm text-center">Reset</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead class="text-left text-xs text-slate-500 uppercase border-b border-slate-200 bg-slate-50/60">
                    <tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($products ?? [] as $p)
                    <tr class="border-b border-slate-100 last:border-0 hover:bg-indigo-50/40 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100 overflow-hidden flex items-center justify-center shrink-0">
                                    @if(!empty($p->image))<img src="{{ \Illuminate\Support\Facades\Storage::url($p->image) }}" class="w-full h-full object-cover" alt="">@else<span>📦</span>@endif
                                </div>
                                <span class="font-bold">{{ $p->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3"><x-badge type="muted">{{ $p->category->name ?? '-' }}</x-badge></td>
                        <td class="px-4 py-3 font-extrabold whitespace-nowrap">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if($p->stock <= 0)<x-badge type="danger">Habis ({{ $p->stock }})</x-badge>
                            @elseif($p->stock <= ($p->minimum_stock ?? 5))<x-badge type="warning">Rendah ({{ $p->stock }})</x-badge>
                            @else<x-badge type="success">{{ $p->stock }}</x-badge>@endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <button @click="editId = {{ $p->id }}" class="px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-bold hover:border-indigo-300 hover:text-indigo-700 transition">Edit</button>
                                <form method="POST" action="{{ route('products.destroy', $p->id) }}" onsubmit="return confirm('Hapus produk {{ addslashes($p->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-600 text-xs font-bold hover:bg-red-100 transition">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- Edit modal per row --}}
                    <div x-show="editId === {{ $p->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="editId = null"></div>
                        <form method="POST" action="{{ route('products.update', $p->id) }}" enctype="multipart/form-data" class="relative bg-white border border-slate-200 rounded-3xl w-full max-w-md p-6 space-y-3 shadow-2xl max-h-[90vh] overflow-y-auto">
                            @csrf @method('PUT')
                            <h3 class="font-extrabold text-lg">Edit Produk</h3>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Nama produk</label>
                                <input name="name" value="{{ $p->name }}" required class="input mt-1" placeholder="Nama produk">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Kategori</label>
                                <select name="category_id" class="input mt-1">
                                    @foreach($categories ?? [] as $cat)<option value="{{ $cat->id }}" @selected($p->category_id==$cat->id)>{{ $cat->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Harga</label>
                                    <input name="price" type="number" min="0" value="{{ $p->price }}" required class="input mt-1" placeholder="Harga">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Stok</label>
                                    <input name="stock" type="number" min="0" value="{{ $p->stock }}" required class="input mt-1" placeholder="Stok">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Min. stok (peringatan rendah)</label>
                                <input name="minimum_stock" type="number" min="0" value="{{ $p->minimum_stock ?? 5 }}" class="input mt-1" placeholder="Min. stok">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Foto (opsional)</label>
                                <input name="image" type="file" accept="image/*" class="input mt-1">
                            </div>
                            <div class="flex gap-2 pt-1"><button type="button" @click="editId=null" class="btn-soft flex-1">Batal</button><button class="btn-primary flex-1">Update</button></div>
                        </form>
                    </div>
                    @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada produk" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($products ?? [], 'links'))
    <div class="mt-4">{{ $products->links() }}</div>
    @endif

    {{-- Add modal --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="addOpen = false"></div>
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="relative bg-white border border-slate-200 rounded-3xl w-full max-w-md p-6 space-y-3 shadow-2xl max-h-[90vh] overflow-y-auto">
            @csrf
            <h3 class="font-extrabold text-lg">Tambah Produk</h3>
            <div>
                <label class="text-xs font-semibold text-slate-500">Nama produk</label>
                <input name="name" required class="input mt-1" placeholder="cth: Kopi Susu">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Kategori</label>
                <select name="category_id" class="input mt-1">
                    @foreach($categories ?? [] as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Harga</label>
                    <input name="price" type="number" min="0" required class="input mt-1" placeholder="18000">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Stok awal</label>
                    <input name="stock" type="number" min="0" required class="input mt-1" placeholder="20">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Min. stok</label>
                <input name="minimum_stock" type="number" min="0" value="5" class="input mt-1" placeholder="5">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Foto (opsional)</label>
                <input name="image" type="file" accept="image/*" class="input mt-1">
            </div>
            <div class="flex gap-2 pt-1"><button type="button" @click="addOpen=false" class="btn-soft flex-1">Batal</button><button class="btn-primary flex-1">Simpan</button></div>
        </form>
    </div>

    {{-- Category modal --}}
    <div x-show="catOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="catOpen = false"></div>
        <div class="relative bg-white border border-slate-200 rounded-3xl w-full max-w-sm p-5 shadow-2xl">
            <h3 class="font-extrabold mb-1">Kategori</h3>
            <p class="text-xs text-slate-500 mb-3">Kategori yang masih punya produk tidak bisa dihapus.</p>
            <form method="POST" action="{{ route('categories.store') }}" class="flex gap-2 mb-3">
                @csrf
                <input name="name" required class="input" placeholder="Nama kategori baru">
                <button class="btn-primary shrink-0">+</button>
            </form>
            <div class="space-y-2 max-h-56 overflow-y-auto">
                @forelse($categories ?? [] as $cat)
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm">
                        <span class="flex-1 font-semibold truncate">{{ $cat->name }} <span class="text-slate-400 font-normal">({{ $cat->products_count ?? $cat->products()->count() }})</span></span>
                        <form method="POST" action="{{ route('categories.destroy', $cat->id) }}" onsubmit="return confirm('Hapus kategori {{ addslashes($cat->name) }}?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 font-bold px-1">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada kategori.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
