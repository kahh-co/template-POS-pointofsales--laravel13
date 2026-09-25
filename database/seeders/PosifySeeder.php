<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PosifySeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo — sinkron dengan kotak "Akun demo" di auth/login.blade.php
        // dan tabel login di CUSTOMIZE.md. Jangan ubah password di satu tempat saja.
        User::updateOrCreate(
            ['email' => 'admin@posify.id'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin321'),
                'role' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@posify.id'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@posify.id'],
            [
                'name' => 'Kasir Andi',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        $categories = [];
        foreach (['Drink', 'Food'] as $name) {
            $categories[$name] = Category::firstOrCreate(['name' => $name]);
        }

        // Contoh produk minimal (5 item)
        $products = [
            ['name' => 'Kopi Susu', 'category' => 'Drink', 'price' => 18000, 'stock' => 20],
            ['name' => 'Americano', 'category' => 'Drink', 'price' => 15000, 'stock' => 25],
            ['name' => 'Croissant', 'category' => 'Food', 'price' => 15000, 'stock' => 3],
            ['name' => 'Roti Bakar', 'category' => 'Food', 'price' => 12000, 'stock' => 15],
            ['name' => 'Kentang Goreng', 'category' => 'Food', 'price' => 10000, 'stock' => 10],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['name' => $p['name']],
                [
                    'category_id' => $categories[$p['category']]->id,
                    'price' => $p['price'],
                    'stock' => $p['stock'],
                    'minimum_stock' => 5,
                ]
            );
        }

        Setting::firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'POSIFY Coffee House',
                'tax_enabled' => false,
                'tax_percent' => 0,
                'receipt_footer' => 'Terima kasih.',
                'paper_size' => '80mm',
            ]
        );
    }
}
