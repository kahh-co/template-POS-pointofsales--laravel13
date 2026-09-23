<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        $keyword = $request->query('search', $request->query('q'));
        $categoryFilter = $request->query('category', $request->query('category_id'));

        $products = Product::with('category')
            ->search($keyword)
            ->when($categoryFilter, function ($q) use ($categoryFilter) {
                // dukung filter nama kategori (dari view) maupun id
                if (is_numeric($categoryFilter)) {
                    $q->where('category_id', $categoryFilter);
                } else {
                    $q->whereHas('category', fn ($qq) => $qq->where('name', $categoryFilter));
                }
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', compact('products', 'categories'));
    }

    protected function resolveCategoryId(Request $request): ?int
    {
        // dukung category_id (id) maupun category (id atau nama)
        $raw = $request->input('category_id', $request->input('category'));

        if (is_numeric($raw)) {
            return (int) $raw;
        }

        if (is_string($raw) && $raw !== '') {
            return Category::where('name', $raw)->value('id');
        }

        return null;
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['category_id' => $this->resolveCategoryId($request)]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->merge(['category_id' => $this->resolveCategoryId($request)]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->transactionItems()->exists()) {
            return back()->with('error', "Produk \"{$product->name}\" tidak dapat dihapus karena sudah memiliki riwayat penjualan.");
        }

        try {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Produk tidak dapat dihapus karena terkait data transaksi.');
        }

        return back()->with('success', 'Produk berhasil dihapus.');
    }
}
