<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\CustomerCharge;
use App\Models\CustomerReferral;
use App\Models\Product;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\GuestUser;
use App\Models\UserDetail;
use App\Models\Order;
use App\Models\ReceiptUpload;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function singleProduct($id)
    {
        $product = Product::where('id',  "$id")
                    ->orWhere('slug', "$id")
                    ->get();
        return response()->json($product);
    }

    public function searchProduct(Request $request)
    {
        $this->validate($request,[
            'query' => 'required',
        ]);
        $query = $request->input('query');
        $productsearch = Product::where('product_name', 'like', "%$query%")
                           ->orWhere('slug', 'like', "%$query%")
                           ->orWhere('description', 'like', "%$query%")
                           ->get();
        return response()->json($productsearch);
    }

    public function productByBrand($id)
    {
        $brand = Brand::where('id',  "$id")
                            ->orWhere('slug', "$id")
                            ->first();
        $brandId = $brand->id;
        $products = Product::where('brand_id',$brandId)->get();
        return response()->json($products);
    }

    public function getLink(Request $request)
    {

        $this->validate($request,[
            'name' => 'required',
            'email' => 'required',
            'phoneNumber' => 'required',
            'deliveryAddress' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'reference' => 'required',
            'payment_method' => 'required',
            'data' =>"required|array",
            'deliveryFee' => 'required'
        ]);
        $data = $request->input('data');
        if (auth()->user()) {
            $userDetail = new CustomerCharge;
            $userDetail->customer_id = auth()->user()->id;
            $userDetail->reference = $request->input('reference');
            $userDetail->payingfor = 'Product';
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                foreach($data as $order)
                {
                    if (is_array($order)) {
                        $orders = new Order;
                        $orders->product_name = $order['product_name'];
                        $orders->qty = $order['qty'];
                        $orders->price = $order['price'];
                        $orders->reference = $request->input('reference');
                        $orders->payingfor = 'Product';
                        $orders->status = 'Pending';
                        $orders->customer_id = auth()->user()->id;
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                    } else {
                        echo "Not an array.\n";
                    }
                }
                if ($request->input('referralCode') !== null) {
                    $referral = Customer::where('refferal_id',$request->input('referralCode'))->first();
                    $referral_id = $referral->id;
                    $referral_point = $referral->points;
                    $new_referral_point = $referral_point + 100;
                    $updateReferralPoint = Customer::where('refferal_id',$request->input('referralCode'))->update(['points'=>$new_referral_point]);
                    $refer = new CustomerReferral;
                    $refer->customer_id = $referral_id;
                    $refer->customer_referral_id = $request->input('referralCode');
                    $refer->name = $request->input('name');
                    $refer->email = $request->input('email');
                    $refer->phone = $request->input('phoneNumber');
                    $refer->save();
                }
                if ($request->payment_method  == "PayStack") {
                    $curl = curl_init();
                    $email = $request->input('email');
                    $tot = $request->input('amount');
                    $total = $tot * 100;
                    $amount = $total;
                    $reference = $request->input('reference');
                    $fullname = $request->lastName.' '.$request->firstName;

                    // url to go to after payment
                    $callback_url = 'http://127.0.0.1:8000/api/v2/product/paystack/callback';

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => json_encode([
                        'amount'=>$amount,
                        'email'=>$email,
                        'callback_url' => $callback_url,
                        'reference' => $reference,
                        'name' => $fullname,
                        'phoneNumber'=>$request->phoneNumber,
                        ]),
                        CURLOPT_HTTPHEADER => [
                        "authorization: Bearer sk_test_4e8fd1e07801aa989c6599d9dbcf911fe06ba691", //replace this with your own test key
                        "content-type: application/json",
                        "cache-control: no-cache"
                        ],
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    if($err){
                        // there was an error contacting the Paystack API
                        die('Curl returned error: ' . $err);
                    }

                    $tranx = json_decode($response, true);

                    if(!$tranx['status']){
                        // there was an error from the API
                        print_r('API returned error: ' . $tranx['message']);
                    }

                    return response()->json(['response' => 'success', 'link' => $tranx['data']['authorization_url']]);
                }elseif ($request->payment_method  == "Fincra") {
                    //Fincra gateway
                }
            } else {
                return $this->error('','Internal Server Error',500);
            }
        } else {
            $userDetail = new UserDetail;
            $userDetail->name = $request->input('name');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->phone = $request->input('phoneNumber');
            $userDetail->email = $request->input('email');
            $userDetail->reference = $request->input('reference');
            $userDetail->status = 0;
            $userDetail->payingfor = 'Product';
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->service_charge = $request->input('serviceCharge');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                foreach($data as $order)
                {
                    if (is_array($order)) {
                        $orders = new Order;
                        $orders->product_name = $order['product_name'];
                        $orders->qty = $order['qty'];
                        $orders->price = $order['price'];
                        $orders->reference = $request->input('reference');
                        $orders->payingfor = 'Product';
                        $orders->status = 'Pending';
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                    } else {
                        echo "Not an array.\n";
                    }
                }
                if ($request->input('referralCode') !== null) {
                    $referral = Customer::where('refferal_id',$request->input('referralCode'))->first();
                    $referral_id = $referral->id;
                    $referral_point = $referral->points;
                    $new_referral_point = $referral_point + 100;
                    $updateReferralPoint = Customer::where('refferal_id',$request->input('referralCode'))->update(['points'=>$new_referral_point]);
                    $refer = new CustomerReferral;
                    $refer->customer_id = $referral_id;
                    $refer->customer_referral_id = $request->input('referralCode');
                    $refer->name = $request->input('name');
                    $refer->email = $request->input('email');
                    $refer->phone = $request->input('phoneNumber');
                    $refer->save();
                }
                if ($request->payment_method  == "PayStack") {
                    $curl = curl_init();
                    $email = $request->input('email');
                    $tot = $request->input('amount');
                    $total = $tot * 100;
                    $amount = $total;
                    $reference = $request->input('reference');
                    $fullname = $request->lastName.' '.$request->firstName;

                    // url to go to after payment
                    $callback_url = 'http://127.0.0.1:8000/api/v2/product/paystack/callback';

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => json_encode([
                        'amount'=>$amount,
                        'email'=>$email,
                        'callback_url' => $callback_url,
                        'reference' => $reference,
                        'name' => $fullname,
                        'phoneNumber'=>$request->phoneNumber,
                        ]),
                        CURLOPT_HTTPHEADER => [
                        "authorization: Bearer sk_test_4e8fd1e07801aa989c6599d9dbcf911fe06ba691", //replace this with your own test key
                        "content-type: application/json",
                        "cache-control: no-cache"
                        ],
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    if($err){
                        // there was an error contacting the Paystack API
                        die('Curl returned error: ' . $err);
                    }

                    $tranx = json_decode($response, true);

                    if(!$tranx['status']){
                        // there was an error from the API
                        print_r('API returned error: ' . $tranx['message']);
                    }

                    return response()->json(['response' => 'success', 'link' => $tranx['data']['authorization_url']]);
                }elseif ($request->payment_method  == "Fincra") {
                    //Fincra gateway
                }
            } else {
                return $this->error('','Internal Server Error',500);
            }
        }


    }

    public function paystackProductCallback()
    {
        $curl = curl_init();
        $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
        if (auth()->user()) {
            if(!$reference){
                dd('No reference supplied');
            }else{
                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_HTTPHEADER => [
                    "accept: application/json",
                    "authorization: Bearer sk_test_4e8fd1e07801aa989c6599d9dbcf911fe06ba691",
                    "cache-control: no-cache"
                  ],
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                if($err){
                    // there was an error contacting the Paystack API
                  die('Curl returned error: ' . $err);
                }

                $callback = json_decode($response);

                if(!$callback->status){
                  // there was an error from the API
                  die('API returned error: ' . $callback->message);
                }
                $status = $callback->data->status;

                $userDetail = CustomerCharge::where('reference',$reference)->first();
                $DBreference = $userDetail->reference;
                if ($reference == $DBreference && $status == true) {
                    $userDetail = CustomerCharge::where('reference',$reference)->update(['status'=>1]);
                    return $this->success($callback,'Payment Have been Confirmed');
                }else{
                    return $this->error('','Failed to confirm Payment',401);
                }
            }
        } else {
            if(!$reference){
                dd('No reference supplied');
            }else{
                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_HTTPHEADER => [
                    "accept: application/json",
                    "authorization: Bearer sk_test_4e8fd1e07801aa989c6599d9dbcf911fe06ba691",
                    "cache-control: no-cache"
                  ],
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                if($err){
                    // there was an error contacting the Paystack API
                  die('Curl returned error: ' . $err);
                }

                $callback = json_decode($response);

                if(!$callback->status){
                  // there was an error from the API
                  die('API returned error: ' . $callback->message);
                }
                $status = $callback->data->status;

                $userDetail = UserDetail::where('reference',$reference)->first();
                $DBreference = $userDetail->reference;
                if ($reference == $DBreference && $status == true) {
                    $userDetail = UserDetail::where('reference',$reference)->update(['status'=>1]);
                    return $this->success($callback,'Payment Have been Confirmed');
                }else{
                    return $this->error('','Failed to confirm Payment',401);
                }
            }
        }
    }

    public function uploadProductReceipt(Request $request)
    {
        $validator = Validator::make($request->all(),
                         [
                            'name' => 'required',
                            'email' => 'required',
                            'phoneNumber' => 'required',
                            'deliveryAddress' => 'required',
                            'amount' => 'required',
                            'currency' => 'required',
                            'reference' => 'required',
                            'payment_method' => 'required',
                            'data' =>"required",
                            'receipt' => 'required|mimes:jpeg,jpg,pdf,png|max:5120',
                            'deliveryFee' => 'required'
                         ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $data = $request->input('data');
        if (auth()->user()) {
            $userDetail = new CustomerCharge;
            $userDetail->customer_id = auth()->user()->id;
            $userDetail->reference = $request->input('reference');
            $userDetail->payingfor = 'Product';
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                foreach($data as $order)
                {
                    if (is_array($order)) {
                        $orders = new Order;
                        $orders->product_name = $order['product_name'];
                        $orders->qty = $order['qty'];
                        $orders->price = $order['price'];
                        $orders->reference = $request->input('reference');
                        $orders->payingfor = 'Product';
                        $orders->status = 'Pending';
                        $orders->customer_id = auth()->user()->id;
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                    } else {
                        echo "Not an array.\n";
                    }
                }
                if ($request->input('referralCode') !== null) {
                    $referral = Customer::where('refferal_id',$request->input('referralCode'))->first();
                    $referral_id = $referral->id;
                    $referral_point = $referral->points;
                    $new_referral_point = $referral_point + 100;
                    $updateReferralPoint = Customer::where('refferal_id',$request->input('referralCode'))->update(['points'=>$new_referral_point]);
                    $refer = new CustomerReferral;
                    $refer->customer_id = $referral_id;
                    $refer->customer_referral_id = $request->input('referralCode');
                    $refer->name = $request->input('name');
                    $refer->email = $request->input('email');
                    $refer->phone = $request->input('phoneNumber');
                    $refer->save();
                }
                if($request->hasFile('receipt')){
                    $fileNameWithExt = $request->file('receipt')->getClientOriginalName();
                    $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
                    $ext = $request->file('receipt')->getClientOriginalExtension();
                    $fileNameToStore = $fileName.'_'.time().'.'.$ext;
                    $path = $request->file('receipt')->storeAs('public/receipt/receipt_files',$fileNameToStore);
                }
                $receipt = new ReceiptUpload;
                $receipt->first_name = $request->input('name');
                $receipt->email = $request->input('email');
                $receipt->reference = $request->input('reference');
                $receipt->phone_number = $request->input('phoneNumber');
                $receipt->receipt = $fileNameToStore;
                $receipt->type = 'Single Buy';
                $save3 = $receipt->save();
                if ($save3) {
                    return $this->success('','Receipt Has been uploaded Successfully',200);
                } else {
                    return $this->error('','Internal Server Error',500);
                }
            } else {
                return $this->error('','Internal Server Error',500);
            }
        } else {
            $userDetail = new UserDetail;
            $userDetail->name = $request->input('name');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->phone = $request->input('phoneNumber');
            $userDetail->email = $request->input('email');
            $userDetail->reference = $request->input('reference');
            $userDetail->status = 0;
            $userDetail->payingfor = 'Product';
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->service_charge = $request->input('serviceCharge');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                foreach($data as $order)
                {
                    if (is_array($order)) {
                        $orders = new Order;
                        $orders->product_name = $order['product_name'];
                        $orders->qty = $order['qty'];
                        $orders->price = $order['price'];
                        $orders->reference = $request->input('reference');
                        $orders->payingfor = 'Product';
                        $orders->status = 'Pending';
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                    } else {
                        echo "Not an array.\n";
                    }
                }
                if ($request->input('referralCode') !== null) {
                    $referral = Customer::where('refferal_id',$request->input('referralCode'))->first();
                    $referral_id = $referral->id;
                    $referral_point = $referral->points;
                    $new_referral_point = $referral_point + 100;
                    $updateReferralPoint = Customer::where('refferal_id',$request->input('referralCode'))->update(['points'=>$new_referral_point]);
                    $refer = new CustomerReferral;
                    $refer->customer_id = $referral_id;
                    $refer->customer_referral_id = $request->input('referralCode');
                    $refer->name = $request->input('name');
                    $refer->email = $request->input('email');
                    $refer->phone = $request->input('phoneNumber');
                    $refer->save();
                }
                if($request->hasFile('receipt')){
                    $fileNameWithExt = $request->file('receipt')->getClientOriginalName();
                    $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
                    $ext = $request->file('receipt')->getClientOriginalExtension();
                    $fileNameToStore = $fileName.'_'.time().'.'.$ext;
                    $path = $request->file('receipt')->storeAs('public/receipt/receipt_files',$fileNameToStore);
                }
                $receipt = new ReceiptUpload;
                $receipt->first_name = $request->input('name');
                $receipt->email = $request->input('email');
                $receipt->reference = $request->input('reference');
                $receipt->phone_number = $request->input('phoneNumber');
                $receipt->receipt = $fileNameToStore;
                $receipt->type = 'Single Buy';
                $save3 = $receipt->save();
                if ($save3) {
                    return $this->success('','Receipt Has been uploaded Successfully',200);
                } else {
                    return $this->error('','Internal Server Error',500);
                }
            } else {
                return $this->error('','Internal Server Error',500);
            }
        }


    }

    public function priceRange(Request $request)
    {
        $query = Product::query();

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $products = $query->get();

        return response()->json(['data' => $products]);
    }

    public function sortPrice()
    {
        if (request()->sort_order == 'low_high') {
            $products = Product::orderBy('price')->get();
        } elseif (request()->sort_order == 'high_low') {
            $products = Product::orderBy('price','desc')->get();
        }
        return response()->json(['data' => $products]);
    }

    public function brandFilter(Request $request)
    {
        $brandIds = $request->input('brand_ids');
        $products = Product::whereIn('brand_id', $brandIds)->get();
        return response()->json($products);
    }

    public function latestProducts(Request $request)
    {
        $product = Product::latest()->inRandomOrder()->take(6)->get();
        return response()->json($product);
    }

    public function recommendProducts(Request $request)
    {
        $product = Product::inRandomOrder()->take(6)->get();
        return response()->json($product);
    }
}
