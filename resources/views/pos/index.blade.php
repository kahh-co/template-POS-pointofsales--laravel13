@extends('layouts.app')
@section('title', 'Kasir')
@section('content')
@php $posProducts = $products ?? []; @endphp
<div x-data="posStore(@js($posProducts), {{ ($taxEnabled ?? false) ? 'true' : 'false' }}, {{ $taxPercent ?? 0 }})" x-cloak>

    {{-- toast --}}
    <div x-show="toast" x-transition class="fixed top-4 left-1/2 -translate-x-1/2 z-[60] bg-slate-900 text-white text-sm font-semibold px-4 py-2.5 rounded-2xl shadow-xl" x-text="toast"></div>

    <div class="flex flex-col lg:flex-row gap-4">
        {{-- Product area --}}
        <div class="flex-1 min-w-0">
            <div class="flex gap-2 mb-3">
                <input x-model="search" type="text" placeholder="🔍 Cari produk..." class="input">
            </div>
            <div class="flex gap-2 overflow-x-auto pb-2 mb-3 no-print">
                <button @click="category='All'" :class="category==='All' ? 'pill active border border-transparent' : 'pill bg-white border border-slate-200 text-slate-600'" class="shrink-0 text-xs font-bold px-4 py-2 rounded-full">Semua</button>
                @foreach($categories ?? [] as $cat)
                    <button @click="category='{{ $cat->name }}'" :class="category==='{{ $cat->name }}' ? 'pill active border border-transparent' : 'pill bg-white border border-slate-200 text-slate-600'" class="shrink-0 text-xs font-bold px-4 py-2 rounded-full">{{ $cat->name }}</button>
                @endforeach
                <button @click="showCatModal = true" class="shrink-0 text-xs font-bold px-3 py-2 rounded-full bg-white border border-dashed border-indigo-300 text-indigo-600">+</button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                <template x-for="p in filtered()" :key="p.id">
                    <button @click="add(p)" :disabled="p.stock <= 0"
                        class="card card-hover p-3 text-left relative disabled:opacity-60 overflow-hidden">
                        <div class="aspect-square rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100/60 overflow-hidden mb-2 flex items-center justify-center">
                            <template x-if="p.image"><img :src="p.image" class="w-full h-full object-cover" :alt="p.name"></template>
                            <template x-if="!p.image"><span class="text-3xl">📦</span></template>
                        </div>
                        <p class="font-semibold text-sm truncate" x-text="p.name"></p>
                        <p class="text-xs text-indigo-600 font-bold mt-0.5" x-text="rupiah(p.price)"></p>
                        <span x-show="p.stock <= 0" class="absolute top-2 right-2 text-[10px] px-2 py-0.5 rounded-full bg-[#EF4444]/20 text-red-600 border border-[#EF4444]/30 font-bold">OUT</span>
                        <span x-show="p.stock > 0 && p.stock <= p.minimum_stock" class="absolute top-2 right-2 text-[10px] px-2 py-0.5 rounded-full bg-[#EF4444]/20 text-red-600 border border-[#EF4444]/30 font-bold">LOW</span>
                        <p class="text-[11px] text-slate-500 mt-1">Stok: <span x-text="p.stock"></span></p>
                    </button>
                </template>
            </div>
            <div x-show="filtered().length === 0" class="card mt-3"><x-empty-state title="Produk tidak ditemukan" /></div>
        </div>

        {{-- Cart desktop --}}
        <aside class="hidden lg:block w-96 shrink-0">
            <div class="card p-5 sticky top-20">
                <h2 class="font-bold mb-4">Your Cart <span class="text-xs text-slate-500" x-text="'(' + cart.length + ')'"></span></h2>
                <template x-if="cart.length === 0"><p class="text-sm text-slate-500 text-center py-8">Keranjang kosong.<br>Klik produk untuk menambah.</p></template>
                <div class="space-y-3 max-h-72 overflow-y-auto mb-4">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold truncate" x-text="item.name"></p>
                                <p class="text-xs text-slate-500" x-text="rupiah(item.price)"></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="dec(item)" class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200">−</button>
                                <span class="w-6 text-center font-bold" x-text="item.qty"></span>
                                <button @click="inc(item)" class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200">+</button>
                            </div>
                            <p class="w-20 text-right font-bold text-xs" x-text="rupiah(item.price * item.qty)"></p>
                            <button @click="remove(item)" class="text-red-400">✕</button>
                        </div>
                    </template>
                </div>
                <div class="space-y-2 text-sm border-t border-slate-200 pt-3">
                    <div class="flex justify-between text-slate-500"><span>Subtotal</span><span x-text="rupiah(subtotal())"></span></div>
                    <div class="flex justify-between items-center text-slate-500"><span>Discount</span><input type="number" min="0" x-model.number="discount" class="input !w-28 !py-1 text-right"></div>
                    <div class="flex justify-between text-slate-500" x-show="taxEnabled"><span x-text="'Tax (' + taxPercent + '%)'"></span><span x-text="rupiah(tax())"></span></div>
                    <div class="flex justify-between font-extrabold text-lg"><span>Total</span><span x-text="rupiah(total())"></span></div>
                </div>
                <button @click="openCheckout()" :disabled="cart.length===0" class="btn-primary w-full mt-4 !py-3">PAY NOW →</button>
            </div>
        </aside>
    </div>

    {{-- Mobile floating bar --}}
    <div x-show="cart.length > 0" class="lg:hidden no-print fixed bottom-16 inset-x-3 z-40">
        <button @click="showCart = true" class="w-full bg-primary rounded-2xl px-4 py-3.5 flex items-center justify-between font-bold shadow-xl">
            <span x-text="cartCount() + ' items'"></span>
            <span x-text="rupiah(total())"></span>
        </button>
    </div>

    {{-- Mobile cart drawer --}}
    <div x-show="showCart" class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-slate-900/40" @click="showCart = false"></div>
        <div class="absolute bottom-0 inset-x-0 bg-white border-t border-slate-200 rounded-t-2xl p-4 max-h-[85vh] overflow-y-auto">
            <div class="w-10 h-1 rounded bg-[#262626] mx-auto mb-3"></div>
            <h2 class="font-bold mb-3">Your Cart</h2>
            <template x-for="item in cart" :key="item.id">
                <div class="flex items-center gap-2 text-sm py-2 border-b border-slate-200">
                    <div class="flex-1"><p class="font-semibold" x-text="item.name"></p><p class="text-xs text-slate-500" x-text="rupiah(item.price)"></p></div>
                    <button @click="dec(item)" class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200">−</button>
                    <span class="w-6 text-center font-bold" x-text="item.qty"></span>
                    <button @click="inc(item)" class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200">+</button>
                    <button @click="remove(item)" class="text-red-400 ml-1">✕</button>
                </div>
            </template>
            <div class="flex justify-between text-sm mt-3 text-slate-500"><span>Subtotal</span><span x-text="rupiah(subtotal())"></span></div>
            <div class="flex justify-between items-center text-sm text-slate-500 mt-1"><span>Discount</span><input type="number" min="0" x-model.number="discount" class="input !w-28 !py-1 text-right"></div>
            <div class="flex justify-between font-extrabold text-lg mt-2"><span>Total</span><span x-text="rupiah(total())"></span></div>
            <button @click="openCheckout()" :disabled="cart.length===0" class="btn-primary w-full mt-3 !py-3">PAY NOW →</button>
        </div>
    </div>

    {{-- Checkout modal --}}
    <div x-show="showCheckout" class="fixed inset-0 z-[55] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/40" @click="showCheckout = false"></div>
        <div class="relative bg-white border border-slate-200 rounded-t-2xl sm:rounded-2xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="font-bold text-lg">Checkout</h2>
            <p class="text-4xl font-black my-4" x-text="rupiah(total())"></p>
            <p class="text-xs text-slate-500 font-semibold mb-2">PAYMENT METHOD</p>
            <div class="grid grid-cols-4 gap-2 mb-4">
                <template x-for="m in ['Cash','QRIS','Debit','Transfer']" :key="m">
                    <button @click="payment = m" :class="payment===m ? 'border-transparent text-white shadow-lg shadow-indigo-600/30' : 'border-slate-200 text-slate-500 bg-white'" :style="payment===m ? 'background:linear-gradient(135deg,#4F46E5,#7C3AED)' : ''" class="border rounded-xl py-2.5 text-xs font-bold" x-text="m"></button>
                </template>
            </div>
            <div x-show="payment==='Cash'">
                <p class="text-xs text-slate-500 font-semibold mb-2">CASH RECEIVED</p>
                <div class="flex gap-2 mb-2 flex-wrap">
                    <template x-for="n in [10000,20000,50000,100000]" :key="n">
                        <button @click="cash = n" class="text-xs px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200" x-text="rupiah(n)"></button>
                    </template>
                    <button @click="cash = total()" class="text-xs px-3 py-1.5 rounded-full bg-primary/20 border border-primary/40 text-indigo-600">Uang Pas</button>
                </div>
                <input type="number" min="0" x-model.number="cash" class="input" placeholder="Nominal tunai">
                <div class="flex justify-between mt-3 text-sm"><span class="text-slate-500">Change</span><span class="font-bold text-green-600" x-text="rupiah(change())"></span></div>
            </div>
            <button @click="checkout()" :disabled="loading || cart.length===0 || (payment==='Cash' && cash < total())" class="btn-primary w-full mt-5 !py-3" x-text="loading ? 'PROCESSING...' : 'COMPLETE PAYMENT'"></button>
        </div>
    </div>

    {{-- Success modal --}}
    <div x-show="showSuccess" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white border border-slate-200 rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl">
            <div class="w-16 h-16 rounded-full flex text-3xl items-center justify-center mx-auto mb-3 text-white shadow-lg shadow-emerald-500/40" style="background:linear-gradient(135deg,#10B981,#16A34A)">✓</div>
            <h2 class="font-extrabold text-lg">Pembayaran Berhasil!</h2>
            <p class="font-mono text-sm text-slate-500" x-text="lastInvoice"></p>
            <p class="text-2xl font-black my-2" x-text="rupiah(lastTotal)"></p>
            <p class="text-sm text-slate-500" x-show="lastChange > 0">Kembalian: <b class="text-emerald-600" x-text="rupiah(lastChange)"></b></p>
            <div class="grid grid-cols-2 gap-2 mt-5">
                <a :href="'/transactions/' + lastId + '/receipt'" class="btn-soft text-center">🖨 Struk</a>
                <button @click="reset()" class="btn-primary">Transaksi Baru</button>
            </div>
        </div>
    </div>

    {{-- Quick category modal --}}
    <div x-show="showCatModal" class="fixed inset-0 z-[55] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40" @click="showCatModal = false"></div>
        <form method="POST" action="{{ route('categories.store') }}" class="relative bg-white border border-slate-200 rounded-2xl w-full max-w-xs p-5">
            @csrf
            <h3 class="font-bold mb-3">Kategori Cepat</h3>
            <input name="name" required class="input" placeholder="Nama kategori">
            <button class="btn-primary w-full mt-3">Simpan</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function posStore(products, taxEnabled, taxPercent) {
  return {
    products, taxEnabled, taxPercent,
    cart: [], search: '', category: 'All',
    discount: 0, payment: 'Cash', cash: 0,
    showCart: false, showCheckout: false, showSuccess: false, showCatModal: false,
    loading: false, toast: '', lastInvoice: '', lastTotal: 0, lastId: null, lastChange: 0,
    rupiah(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID'); },
    filtered(){
      const q = this.search.toLowerCase();
      return this.products.filter(p =>
        (this.category==='All' || p.category===this.category) &&
        p.name.toLowerCase().includes(q));
    },
    find(id){ return this.products.find(p => p.id===id); },
    cartCount(){ return this.cart.reduce((s,i)=>s+i.qty,0); },
    add(p){
      if (p.stock <= 0) { this.fail('Stok habis!'); return; }
      const ex = this.cart.find(i=>i.id===p.id);
      const qty = ex ? ex.qty+1 : 1;
      if (qty > p.stock) { this.fail('Stok tidak cukup! Sisa ' + p.stock); return; }
      ex ? ex.qty++ : this.cart.push({id:p.id,name:p.name,price:p.price,qty:1});
    },
    inc(item){ const p=this.find(item.id); if(item.qty+1>p.stock){this.fail('Stok tidak cukup!');return;} item.qty++; },
    dec(item){ item.qty<=1 ? this.remove(item) : item.qty--; },
    remove(item){ this.cart = this.cart.filter(i=>i.id!==item.id); },
    subtotal(){ return this.cart.reduce((s,i)=>s+i.price*i.qty,0); },
    tax(){ return this.taxEnabled ? Math.round((this.subtotal()-this.discount)*this.taxPercent/100) : 0; },
    total(){ return Math.max(0, this.subtotal()-this.discount+this.tax()); },
    change(){ return Math.max(0, this.cash - this.total()); },
    fail(msg){ this.toast=msg; setTimeout(()=>this.toast='',2500); },
    openCheckout(){ this.showCart=false; this.cash=this.total(); this.showCheckout=true; },
    async checkout(){
      this.loading = true;
      try {
        const res = await fetch("{{ route('pos.checkout') }}", {
          method: 'POST',
          headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content },
          body: JSON.stringify({ items: this.cart.map(i=>({product_id:i.id, quantity:i.qty})), discount:Number(this.discount||0), payment_method:String(this.payment||'cash').toLowerCase(), payment_amount:this.payment==='Cash'?(Number(this.cash)||0):0 })
        });
        const data = await res.json();
        if(!res.ok) {
          const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Checkout gagal');
          throw new Error(msg);
        }
        const trx = data.transaction || data;
        this.lastInvoice = trx.invoice_no || trx.invoice || ''; this.lastTotal = trx.total ?? this.total(); this.lastId = trx.id;
        this.lastChange = trx.change ?? 0;
        // kurangi stok lokal agar tampilan langsung update
        this.cart.forEach(ci => { const p = this.find(ci.id); if (p) p.stock = Math.max(0, p.stock - ci.qty); });
        this.showCheckout=false; this.showSuccess=true;
      } catch(e){ this.fail(e.message); }
      this.loading=false;
    },
    reset(){ this.cart=[]; this.discount=0; this.cash=0; this.payment='Cash'; this.showSuccess=false; }
  };
}
</script>
@endpush
@endsection
