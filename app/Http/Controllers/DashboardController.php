<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $revenueToday = (int) Transaction::whereDate('created_at', today())->sum('total');
        $transactionCount = Transaction::whereDate('created_at', today())->count();
        $itemsSold = (int) TransactionItem::whereHas('transaction', function ($q) {
            $q->whereDate('created_at', today());
        })->sum('quantity');

        $lowStockProducts = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit(5)
            ->get();
        $lowStockCount = Product::whereColumn('stock', '<=', 'minimum_stock')->count();

        $salesChart = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'label' => \Carbon\Carbon::parse($row->date)->format('d M'),
                'total' => (int) $row->total,
            ]);

        $bestSellers = TransactionItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Siapkan data yang dipakai view (nama disamakan + aman untuk @json)
        $byDate = $salesChart->keyBy('label');
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $label = $d->format('d M');
            $chartLabels[] = $label;
            $chartData[] = (int) ($byDate->get($label)['total'] ?? 0);
        }

        // Alias agar view lama tetap jalan
        $todayRevenue = $revenueToday;
        $todayCount = $transactionCount;
        $todayItems = $itemsSold;
        $bestSelling = $bestSellers->map(fn ($it) => [
            'name' => $it->product->name ?? 'Produk',
            'sold' => (int) $it->total_qty,
        ]);

        return view('dashboard.index', compact(
            'revenueToday',
            'transactionCount',
            'itemsSold',
            'lowStockCount',
            'lowStockProducts',
            'salesChart',
            'bestSellers',
            'recentTransactions',
            'chartLabels',
            'chartData',
            'todayRevenue',
            'todayCount',
            'todayItems',
            'bestSelling'
        ));
    }
}
