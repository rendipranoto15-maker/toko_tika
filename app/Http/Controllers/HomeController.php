<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (
            Auth::check() &&
            Auth::user()->role &&
            Auth::user()->role->role_name === 'admin'
        ) {
            return redirect()->route('admin.dashboard');
        }

        $categories = Category::whereIn('category_name', ['Sembako', 'Bumbu Dapur'])
            ->orderByRaw("
                CASE 
                    WHEN category_name = 'Sembako' THEN 1
                    WHEN category_name = 'Bumbu Dapur' THEN 2
                    ELSE 3
                END
            ")
            ->get();

        $products = collect();
        foreach ($categories as $category) {
            $catProducts = Product::with(['category', 'variants'])
                ->where('status', 'active')
                ->where('category_id', $category->id)
                ->latest()
                ->take(8)
                ->get();
            $products = $products->merge($catProducts);
        }

        return view('home', compact('categories', 'products'));
    }
}