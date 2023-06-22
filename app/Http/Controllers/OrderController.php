<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\GuestUser;
use App\Models\UserDetail;
use App\Models\Order;
use App\Models\BulkUserDetail;
use App\Models\BulkOrder;


class OrderController extends Controller
{
    public function getOrder(Request $request)
    {
        $id = $request->id;
        $reference = $request->reference;
        $orders = Order::where(['user_id' => $id, 'reference' => $reference])->get();
        $userDetail = UserDetail::where(['user_id' => $id, 'reference' => $reference])->get();
        return view('admin.orders')->with(['orders' => $orders, 'userDetail' => $userDetail]);
    }

    public function getBulkOrder(Request $request)
    {
        $id = $request->id;
        $reference = $request->reference;
        $orders = BulkOrder::where(['reference' => $reference])->get();
        $userDetail = BulkUserDetail::where(['id' => $id, 'reference' => $reference])->get();
        return view('admin.bulkorders')->with(['orders' => $orders, 'userDetails' => $userDetail]);
    }

    public function getOrderDetails()
    {
        $userDetails = UserDetail::all();

        return view('admin.orderdetails')->with('userDetails',$userDetails);
    }

    public function getFoodOrderDetails()
    {
        $userDetails = UserDetail::all();

        return view('admin.food-order-details')->with('userDetails',$userDetails);
    }

    public function getBulkOrderDetails()
    {
        $userDetails = BulkUserDetail::all();

        return view('admin.bulkorderdetails')->with('userDetails',$userDetails);
    }
}
