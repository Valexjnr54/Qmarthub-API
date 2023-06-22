<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCharge;
use App\Models\CustomerReferral;
use App\Models\Order;
use App\Models\OrderDrink;
use App\Models\OrderExtra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function orders()
    {
        $userId = auth()->user()->id;
        $orders = Order::where('customer_id',$userId)->get();
        if(!$orders->isEmpty()){
            return response()->json($orders);
        }else{
            return response()->json(['message' => 'No Order has been placed by You']);
        }

    }

    public function singleOrders($reference)
    {
        $userId = auth()->user()->id;
        $orderDetail = CustomerCharge::where(['customer_id' => $userId,'reference' => $reference])->first();
        $payingfor = $orderDetail->payingfor;
        if($payingfor == 'Food'){
            $orders = Order::where(['customer_id' => $userId,'reference' => $reference])->get();
            $orderDrink = OrderDrink::where(['reference' => $reference])->get();
            $orderExtra = OrderExtra::where(['reference' => $reference])->get();
            return response()->json(['orders' => $orders, 'ordered_drinks' => $orderDrink, 'ordered_toppings' => $orderExtra]);
        }elseif($payingfor == 'Product'){
            $orders = Order::where(['customer_id' => $userId,'reference' => $reference])->get();
            return response()->json(['orders' => $orders]);
        }
    }

    public function refers()
    {
        $userId = auth()->user()->id;
        $userReferralId = auth()->user()->refferal_id;
        $refers = CustomerReferral::where(['customer_id' => $userId,'customer_referral_id' => $userReferralId])->get();
        return response()->json(['refers' => $refers]);
    }

    public function details()
    {
        $userId = auth()->user()->id;
        $details = Customer::where('id',$userId)->get();
        return response()->json($details);
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();


        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['error' => 'Invalid current password'], 400);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully',
            'user' => auth()->user(),
        ]);
    }

    public function changeLocation(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        // Update the address
        $user->address = $request->address;
        $user->save();

        return response()->json([
            'message' => 'Delivery Address changed successfully',
            'user' => auth()->user(),
        ]);
    }
    public function deleteAccount()
    {
        $userId = auth()->user()->id;
        $customer = Customer::find($userId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);

    }

    public function customerLocation()
    {
        $userId = auth()->user()->id;
        $customer = Customer::find($userId);
        $address = $customer->address;
        return response()->json(['address' => $address]);
    }
}
