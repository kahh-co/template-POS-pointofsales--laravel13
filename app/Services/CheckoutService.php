<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    public function process(
        array $items,
        int $discount = 0,
        ?string $paymentMethod = 'cash',
        int $paymentAmount = 0,
        int $userId = 0
    ): Transaction {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['Keranjang tidak boleh kosong.'],
            ]);
        }

        $paymentMethod = $paymentMethod ?: 'cash';

        return DB::transaction(function () use ($items, $discount, $paymentMethod, $paymentAmount, $userId) {
            $productIds = collect($items)->pluck('product_id')->unique()->values()->all();

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lines = [];

            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => ["Quantity produk ID {$productId} minimal 1."],
                    ]);
                }

                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Produk ID {$productId} tidak ditemukan."],
                    ]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => ["Stok {$product->name} tidak cukup (sisa {$product->stock})."],
                    ]);
                }

                $lineSubtotal = $product->price * $qty;
                $subtotal += $lineSubtotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = max(0, $discount);

            $setting = Setting::current();

            $tax = 0;
            if ($setting->tax_enabled && (float) $setting->tax_percent > 0) {
                $taxBase = max(0, $subtotal - $discount);
                $tax = (int) round($taxBase * ((float) $setting->tax_percent / 100));
            }

            $total = max(0, $subtotal - $discount + $tax);

            if ($paymentMethod === 'cash' && $paymentAmount < $total) {
                throw ValidationException::withMessages([
                    'payment_amount' => ['Nominal pembayaran kurang dari total.'],
                ]);
            }

            $change = $paymentMethod === 'cash' ? max(0, $paymentAmount - $total) : 0;

            $invoiceNo = $this->generateInvoiceNo();

            /** @var Transaction $transaction */
            $transaction = Transaction::create([
                'invoice_no' => $invoiceNo,
                'user_id' => $userId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_amount' => $paymentAmount,
                'change_amount' => $change,
            ]);

            foreach ($lines as $line) {
                $transaction->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            return $transaction->load('items.product');
        });
    }

    protected function generateInvoiceNo(): string
    {
        $date = now()->format('Ymd');
        $countToday = Transaction::whereDate('created_at', today())->count() + 1;

        do {
            $invoice = sprintf('TRX-%s-%03d', $date, $countToday);
            $countToday++;
        } while (Transaction::where('invoice_no', $invoice)->exists());

        return $invoice;
    }
}
