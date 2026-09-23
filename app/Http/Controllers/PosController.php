<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get();
        $setting = Setting::current();

        $products = Product::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (int) $p->price,
                'stock' => (int) $p->stock,
                'minimum_stock' => (int) ($p->minimum_stock ?? 5),
                'category' => $p->category->name ?? 'Umum',
                'image' => $p->image,
            ])
            ->values();

        $taxEnabled = (bool) $setting->tax_enabled;
        $taxPercent = (float) $setting->tax_percent;

        return view('pos.index', compact('categories', 'products', 'setting', 'taxEnabled', 'taxPercent'));
    }

    public function store(Request $request, CheckoutService $checkout): JsonResponse
    {
        // Normalisasi alias dari frontend (id/qty, Cash/QRIS, cash_received/payment_amount)
        $input = $request->all();

        if (isset($input['items']) && is_array($input['items'])) {
            $input['items'] = collect($input['items'])->map(fn ($it) => [
                'product_id' => $it['product_id'] ?? $it['id'] ?? null,
                'quantity' => $it['quantity'] ?? $it['qty'] ?? null,
            ])->values()->all();
        }

        if (isset($input['payment_method']) && is_string($input['payment_method'])) {
            $input['payment_method'] = strtolower($input['payment_method']);
        }

        if (! isset($input['payment_amount']) && isset($input['cash_received'])) {
            $input['payment_amount'] = $input['cash_received'];
        }

        $request->merge($input);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,qris,debit,transfer'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $transaction = $checkout->process(
                $validated['items'],
                (int) ($validated['discount'] ?? 0),
                $validated['payment_method'] ?? 'cash',
                (int) ($validated['payment_amount'] ?? 0),
                (int) $request->user()->id
            );

            return response()->json([
                'success' => true,
                // format flat agar kompatibel dengan JS kasir lama
                'id' => $transaction->id,
                'invoice_no' => $transaction->invoice_no,
                'invoice' => $transaction->invoice_no,
                'total' => $transaction->total,
                'change' => $transaction->change_amount,
                'transaction' => [
                    'id' => $transaction->id,
                    'invoice_no' => $transaction->invoice_no,
                    'total' => $transaction->total,
                    'change' => $transaction->change_amount,
                ],
                'message' => 'Pembayaran berhasil.',
                'redirect' => route('transactions.receipt', $transaction),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
