<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index()
    {
        $featuredStores = Store::where('status', 'active')
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('featuredStores'));
    }
}
