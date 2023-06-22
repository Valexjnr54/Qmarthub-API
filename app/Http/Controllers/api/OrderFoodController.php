<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\GuestUser;
use App\Models\Order;
use App\Models\OrderDrink;
use App\Models\OrderExtra;
use App\Models\ReceiptUpload;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Validator;

class OrderFoodController extends Controller
{
    public function getLink(Request $request)
    {

        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
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
        $userDetail = new UserDetail;
        $userDetail->first_name = $request->input('firstName');
        $userDetail->last_name = $request->input('lastName');
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

    public function paystackFoodCallback()
    {
        $curl = curl_init();
        $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
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
            $firstName = $userDetail->first_name;
            $lastName = $userDetail->last_name;
            $fullname = $lastName." ".$firstName;
            $email = $userDetail->email;
            $guestDetail = GuestUser::where('email',$email)->first();
            $phoneNumber = $guestDetail->phone;
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

    public function uploadFoodReceipt(Request $request)
   {
        $validator = Validator::make($request->all(),
                         [
                            'firstName' => 'required',
                            'lastName' => 'required',
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
        $guest = new GuestUser;
        $guest->first_name = $request->input('firstName');
        $guest->last_name = $request->input('lastName');
        $guest->email = $request->input('email');
        $guest->phone = $request->input('phoneNumber');
        $save = $guest->save();
        if ($save) {
            $guestId = GuestUser::orderBy('id', 'desc')->first()->id;
            $userDetail = new UserDetail;
            $userDetail->first_name = $request->input('firstName');
            $userDetail->last_name = $request->input('lastName');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->email = $request->input('email');
            $userDetail->reference = $request->input('reference');
            $userDetail->status = 0;
            $userDetail->user_id = $guestId;
            $userDetail->payingfor = 'Food';
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $save2 = $userDetail->save();
            if ($save2) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                foreach ($data as $order) {
                    if (is_array($order)) {
                        $orders = new Order;
                        $orders->product_name = $order['product_name'];
                        $orders->qty = $order['qty'];
                        $orders->price = $order['price'];
                        $orders->reference = $request->input('reference');
                        $orders->user_id = $guestId;
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
                if($request->hasFile('receipt')){
                    $fileNameWithExt = $request->file('receipt')->getClientOriginalName();
                    $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
                    $ext = $request->file('receipt')->getClientOriginalExtension();
                    $fileNameToStore = $fileName.'_'.time().'.'.$ext;
                    $path = $request->file('receipt')->storeAs('public/receipt/receipt_files',$fileNameToStore);
                }
                $receipt = new ReceiptUpload;
                $receipt->first_name = $request->input('firstName');
                $receipt->last_name = $request->input('lastName');
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
            return $this->error('','Internal Server Error',500);
        }
    }
}
