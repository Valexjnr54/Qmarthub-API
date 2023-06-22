<?php

namespace App\Http\Controllers;

use App\Models\BulkOrder;
use App\Models\FoodVendor;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $role = Auth::user()->role;
        if($role == '1'){
            $foodOrders = Order::where('payingfor','Food')->get();
            $productOrders = Order::where('payingfor','Product')->get();
            $bulkOrders = BulkOrder::all();
            $vendors = FoodVendor::all();
            return view('admin.dashboard')->with([
                    'foodOrders' => $foodOrders,
                    'productOrders' => $productOrders,
                    'bulkOrders' => $bulkOrders,
                    'vendors' => $vendors
                ]);
        }else{
            return view('user.dashboard');
        }
    }
}
