<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCharge;
use App\Models\CustomerReferral;
use App\Models\Drink;
use App\Models\Extra;
use App\Models\Food;
use App\Models\FoodDrink;
use App\Models\FoodExtra;
use App\Models\GuestUser;
use App\Models\Order;
use App\Models\OrderDrink;
use App\Models\OrderExtra;
use App\Models\ReceiptUpload;
use App\Models\UserDetail;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodsController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $foods = Food::all();
        return response()->json($foods);
    }

    public function singleFood($id)
    {
        $food = Food::where('id',  "$id")
                    ->orWhere('slug', "$id")
                    ->get();
        return response()->json($food);
    }

    public function searchFood(Request $request)
    {
        $this->validate($request,[
            'query' => 'required',
        ]);
        $query = $request->input('query');
        $foodsearch = Food::where('name', 'like', "%$query%")
                           ->orWhere('slug', 'like', "%$query%")
                           ->orWhere('description', 'like', "%$query%")
                           ->get();
        return response()->json($foodsearch);
    }

    public function foodDrinks($id)
    {
        $fooddrinks = FoodDrink::where('food_id',$id)->get();
        $food = Food::where('id',$id)->first();
        $foodName = $food->name;
        $details = array('name' => $foodName);
        if (count($fooddrinks) > 0) {
            foreach ($fooddrinks as $drink) {
                $drinkId = $drink->drink_id;
                $drink = Drink::find($drinkId);
                if ($drink) {
                    $details[] = array('Drinks'=> $drink);
                }
            }
        } else {
            $details[] = array('message' => 'No Drinks Found');
        }


        return response()->json($details);
    }

    public function foodExtras($id)
    {
        $foodextras = FoodExtra::where('food_id',$id)->get();
        $food = Food::where('id',$id)->first();
        $foodName = $food->name;
        // $details = array('name' => $foodName,);
        if (count($foodextras) > 0) {
            foreach ($foodextras as $extra) {
                $extraId = $extra->extra_id;
                $extra = Extra::find($extraId);
                $details[] =  $extra;
            }

        } else {
            $details[] = array('message' => 'No Toppings Found');
        }


        return response()->json($details);
    }

    public function drinks()
    {
        $drinks = Drink::all();
        return response()->json($drinks);
    }

    public function singleDrink($id)
    {
        $drink = Drink::find($id);
        return response()->json($drink);
    }

    public function extras()
    {
        $drinks = Extra::all();
        return response()->json($drinks);
    }

    public function singleExtra($id)
    {
        $drink = Extra::find($id);
        return response()->json($drink);
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
            'deliveryFee' => 'required',
            'serviceCharge' => 'required'
        ]);
        $data = $request->input('data');
        if (auth()->user()) {
            $userDetail = new CustomerCharge;
            $userDetail->customer_id = auth()->user()->id;
            $userDetail->reference = $request->input('reference');
            $userDetail->payingfor = 'Food';
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
                        $orders->payingfor = 'Food';
                        $orders->status = 'Pending';
                        $orders->customer_id = auth()->user()->id;
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                        foreach ($order['drinks'] as  $drinksOrdered) {
                            $orderedDrinks = new OrderDrink;
                            $orderedDrinks->drink_id = $drinksOrdered['id'];
                            $orderedDrinks->name = $drinksOrdered['name'];
                            $orderedDrinks->price = $drinksOrdered['price'];
                            $orderedDrinks->reference = $request->input('reference');
                            $orderedDrinks->save();
                        }
                        foreach ($order['toppings'] as  $extrasOrdered) {
                            $orderedExtras = new OrderExtra;
                            $orderedExtras->extra_id = $extrasOrdered['id'];
                            $orderedExtras->name = $extrasOrdered['name'];
                            $orderedExtras->price = $extrasOrdered['price'];
                            $orderedExtras->reference = $request->input('reference');
                            $orderedExtras->save();
                        }
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
                    $callback_url = 'http://127.0.0.1:8000/api/v2/food/paystack/callback';

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
            $userDetail->payingfor = 'Food';
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->service_charge = $request->input('serviceCharge');
            // $userDetail->amount = $request->input('amount');
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
                        $orders->payingfor = 'Food';
                        $orders->status = 'Pending';
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                        foreach ($order['drinks'] as  $drinksOrdered) {
                            $orderedDrinks = new OrderDrink;
                            $orderedDrinks->drink_id = $drinksOrdered['id'];
                            $orderedDrinks->name = $drinksOrdered['name'];
                            $orderedDrinks->price = $drinksOrdered['price'];
                            $orderedDrinks->reference = $request->input('reference');
                            $orderedDrinks->save();
                        }
                        foreach ($order['toppings'] as  $extrasOrdered) {
                            $orderedExtras = new OrderExtra;
                            $orderedExtras->extra_id = $extrasOrdered['id'];
                            $orderedExtras->name = $extrasOrdered['name'];
                            $orderedExtras->price = $extrasOrdered['price'];
                            $orderedExtras->reference = $request->input('reference');
                            $orderedExtras->save();
                        }
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
                    $callback_url = 'http://127.0.0.1:8000/api/v2/food/paystack/callback';

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

    public function paystackFoodCallback()
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

    public function uploadFoodReceipt(Request $request)
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
            $userDetail->payingfor = 'Food';
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
                        $orders->payingfor = 'Food';
                        $orders->status = 'Pending';
                        $orders->customer_id = auth()->user()->id;
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                        foreach ($order['drinks'] as  $drinksOrdered) {
                            $orderedDrinks = new OrderDrink;
                            $orderedDrinks->drink_id = $drinksOrdered['id'];
                            $orderedDrinks->name = $drinksOrdered['name'];
                            $orderedDrinks->price = $drinksOrdered['price'];
                            $orderedDrinks->reference = $request->input('reference');
                            $orderedDrinks->save();
                        }
                        foreach ($order['toppings'] as  $extrasOrdered) {
                            $orderedExtras = new OrderExtra;
                            $orderedExtras->extra_id = $extrasOrdered['id'];
                            $orderedExtras->name = $extrasOrdered['name'];
                            $orderedExtras->price = $extrasOrdered['price'];
                            $orderedExtras->reference = $request->input('reference');
                            $orderedExtras->save();
                        }
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
                    return $this->success('','Receipt Has been uploaded Successfully, With Auth',200);
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
            $userDetail->payingfor = 'Food';
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
                        $orders->payingfor = 'Food';
                        $orders->status = 'Pending';
                        $orders->day = $day;
                        $orders->month = $month;
                        $orders->year = $year;
                        $orders->save();
                        foreach ($order['drinks'] as  $drinksOrdered) {
                            $orderedDrinks = new OrderDrink;
                            $orderedDrinks->drink_id = $drinksOrdered['id'];
                            $orderedDrinks->name = $drinksOrdered['name'];
                            $orderedDrinks->price = $drinksOrdered['price'];
                            $orderedDrinks->reference = $request->input('reference');
                            $orderedDrinks->save();
                        }
                        foreach ($order['toppings'] as  $extrasOrdered) {
                            $orderedExtras = new OrderExtra;
                            $orderedExtras->extra_id = $extrasOrdered['id'];
                            $orderedExtras->name = $extrasOrdered['name'];
                            $orderedExtras->price = $extrasOrdered['price'];
                            $orderedExtras->reference = $request->input('reference');
                            $orderedExtras->save();
                        }
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
                    return $this->success('','Receipt Has been uploaded Successfully, without Auth',200);
                } else {
                    return $this->error('','Internal Server Error',500);
                }
            } else {
                return $this->error('','Internal Server Error',500);
            }
        }
    }
}
