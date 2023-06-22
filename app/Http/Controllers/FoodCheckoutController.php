<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FoodCheckoutController extends Controller
{
    public function index()
    {
        // if (Cart::instance('default')->count() == 0) {
        //     return redirect()->route('layouts.landing-page-shop');
        // }
        return view('checkout-food');
    }
}
