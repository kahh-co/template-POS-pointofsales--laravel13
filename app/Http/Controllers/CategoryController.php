<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $category = Category::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse|JsonResponse
    {
        if ($category->products()->exists()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki produk.',
                ], 422);
            }

            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
