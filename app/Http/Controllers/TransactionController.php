<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('user')->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($qq) => $qq->where('name', 'like', "%{$search}%"));
            });
        }

        $range = $request->query('filter', $request->query('date', 'all'));

        match ($range) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            '7days' => $query->where('created_at', '>=', now()->subDays(7)),
            default => null,
        };

        $transactions = $query->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['items.product', 'user']);

        $items = $transaction->items->map(fn ($it) => [
            'id' => $it->id,
            'product_id' => $it->product_id,
            'name' => $it->product->name ?? 'Produk',
            'qty' => (int) $it->quantity,
            'quantity' => (int) $it->quantity,
            'price' => (int) $it->price,
            'subtotal' => (int) $it->subtotal,
        ])->values();

        return response()->json([
            'success' => true,
            // bentuk flat agar kompatibel dengan drawer transaksi
            'id' => $transaction->id,
            'invoice_no' => $transaction->invoice_no,
            'created_at' => $transaction->created_at?->format('d M Y H:i'),
            'cashier' => $transaction->user->name ?? '-',
            'payment_method' => $transaction->payment_method,
            'subtotal' => (int) $transaction->subtotal,
            'discount' => (int) $transaction->discount,
            'tax' => (int) $transaction->tax,
            'total' => (int) $transaction->total,
            'items' => $items,
            'transaction' => $transaction,
        ]);
    }

    public function receipt(Transaction $transaction): View
    {
        $transaction->load(['items.product', 'user']);

        return view('receipt.print', compact('transaction'));
    }
}
