<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->invoice_no ?? '' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        #receipt-area { font-family: ui-monospace, Menlo, Consolas, monospace; }
        .dashed { border-top: 1px dashed #000; margin: 8px 0; }
    </style>
</head>
<body class="bg-gray-200">
    <div class="no-print max-w-md mx-auto flex gap-2 p-4">
        <button onclick="window.print()" class="flex-1 bg-[#6366F1] text-white font-bold rounded-lg py-2.5">🖨 Print</button>
        <a href="{{ route('transactions.index') }}" class="flex-1 text-center bg-white border rounded-lg py-2.5 font-semibold">← Back</a>
    </div>

    <div id="receipt-area" style="width:80mm;margin:auto;background:#fff;color:#000;padding:12px;font-size:12px;">
        <div style="text-align:center">
            @if(!empty($settings->logo))<img src="{{ $settings->logo }}" style="width:48px;height:48px;object-fit:cover;margin:0 auto 4px;" alt="">@endif
            <p style="font-weight:bold;font-size:14px;">{{ $settings->store_name ?? 'POSIFY Store' }}</p>
            <p>{{ $settings->address ?? '' }}</p>
            <p>{{ $settings->phone ?? '' }}</p>
        </div>
        <div class="dashed"></div>
        <p>No: {{ $transaction->invoice_no ?? $transaction->id }}</p>
        <p>Tgl: {{ $transaction->created_at?->format('d/m/Y H:i') }}</p>
        <p>Kasir: {{ $transaction->user->name ?? $transaction->cashier ?? '-' }}</p>
        <div class="dashed"></div>
        @foreach($transaction->items ?? $transaction->details ?? [] as $it)
            <p>{{ $it->product->name ?? $it->name }}</p>
            <p style="display:flex;justify-content:space-between;">
                <span>{{ $it->qty }} x {{ number_format($it->price, 0, ',', '.') }}</span>
                <span>{{ number_format($it->subtotal ?? ($it->qty * $it->price), 0, ',', '.') }}</span>
            </p>
        @endforeach
        <div class="dashed"></div>
        <p style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>{{ number_format($transaction->subtotal ?? $transaction->total, 0, ',', '.') }}</span></p>
        @if(!empty($transaction->discount))
        <p style="display:flex;justify-content:space-between;"><span>Discount</span><span>{{ number_format($transaction->discount, 0, ',', '.') }}</span></p>
        @endif
        @if(!empty($transaction->tax))
        <p style="display:flex;justify-content:space-between;"><span>Tax</span><span>{{ number_format($transaction->tax, 0, ',', '.') }}</span></p>
        @endif
        <p style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;"><span>TOTAL</span><span>{{ number_format($transaction->total, 0, ',', '.') }}</span></p>
        <div class="dashed"></div>
        <p style="display:flex;justify-content:space-between;"><span>{{ $transaction->payment_method ?? 'Cash' }}</span><span>{{ number_format($transaction->cash_received ?? $transaction->total, 0, ',', '.') }}</span></p>
        <p style="display:flex;justify-content:space-between;"><span>Kembali</span><span>{{ number_format($transaction->change ?? 0, 0, ',', '.') }}</span></p>
        <div class="dashed"></div>
        <p style="text-align:center;">{{ $settings->receipt_footer ?? 'Terima kasih atas kunjungan Anda!' }}</p>
    </div>
</body>
</html>
