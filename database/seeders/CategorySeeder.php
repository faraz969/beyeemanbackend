<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Foods',
            'Electronics',
            'Fashion',
            'Cosmetics',
            'Home & Garden',
            'Sports & Outdoors',
            'Books',
            'Toys & Games',
            'Health & Beauty',
            'Automotive',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'is_active' => true,
            ]);
        }
    }
}

