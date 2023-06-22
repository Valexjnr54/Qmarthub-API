<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\GuestUser;
use App\Models\UserDetail;
use App\Models\Order;
use App\Models\ReceiptUpload;
use Paystack;


class PaymentGatewayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }
    public function getCheckoutLink(Request $request)
    {
        if (Auth::user()){
            if (Auth::user()->role != 1) {
                return redirect('cart.index');
            }
        }else{
            $this->validate($request,[
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required',
                'phoneNumber' => 'required',
                'deliveryAddress' => 'required',
                'amount' => 'required',
                'currency' => 'required',
                'reference' => 'required',
                'metadata' => 'required',
            ]);
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
                $userDetail->payingfor = 'Product';
                $userDetail->delivery_fee = $request->input('deliveryFee');
                $save2 = $userDetail->save();
                if ($save2) {
                    foreach (Cart::content() as $cart) {
                        $order = new Order;
                        $order->product_name = $cart->name;
                        $order->qty = $cart->qty;
                        $order->price = $cart->price;
                        $order->reference = $request->input('reference');
                        $order->user_id = $guestId;
                        $order->payingfor = 'Product';
                        $order->status = 'Pending';
                        $save3 = $order->save();
                    }
                    if ($save3) {
                        if ($request->payment_option == "PayStack") {
                            try{
                                return Paystack::getAuthorizationUrl()->redirectNow();
                            }catch(\Exception $e) {
                                return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
                            }
                        }elseif ($request->payment_option == "Bank Transfer") {
                            $categories = Category::all();
                            return view('transfer')->with([
                                'firstName' => $request->input('firstName'),
                                'lastName' => $request->input('lastName'),
                                'phoneNumber' => $request->input('phoneNumber'),
                                'reference' => $request->input('reference'),
                                'email' => $request->input('email'),
                                'amount' => $request->input('amount2'),
                                'categories' => $categories,
                                'amount' => $request->amount2,
                                'deliveryFee' => $request->deliveryFee,
                            ]);
                        }else {
                            $fullname = $request->lastName.' '.$request->firstName;
                            $res = new Client();
                            $response = $res->request('POST', 'https://sandboxapi.fincra.com/checkout/payments', [
                                'body' => '{
                                    "customer":{
                                        "name":"'.$fullname.'",
                                        "email":"'.$request->email.'",
                                        "phoneNumber":"'.$request->phoneNumber.'"
                                    },
                                    "metadata": {
                                        "payingfor":"Product",
                                        "payment_method":"Fincra"
                                    },
                                    "amount":"'.$request->amount2.'",
                                    "redirectUrl":"https://qmarthub.com/fincra/callback",
                                    "currency":"'.$request->currency.'",
                                    "reference":"'.$request->reference.'",
                                    "feeBearer":"customer",
                                    "settlementDestination":"wallet"
                                }',
                                'headers' => [
                                'accept' => 'application/json',
                                'api-key' => 'D4CzuyiC8nwutsFGEKVwuNeFuWSPFHOo',
                                'content-type' => 'application/json',
                                'x-business-id' => '63f4cf1bca322c92e6ba04f4',
                                'x-pub-key' => 'pk_test_NjNmNGNmMWNjYTMyMmM3ZjVlYmEwNGY4OjoxMjY2OTk=',
                                ],
                            ]);
                            $callback = json_decode($response->getBody(), true);
                            return redirect($callback['data']['link']);
                        }
                    }
                }
            }
        }
    }

    public function getFoodCheckoutLink(Request $request)
    {
        if (Auth::user()){
            if (Auth::user()->role != 1) {
                return redirect('cart.index');
            }
        }else{
            $this->validate($request,[
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required',
                'phoneNumber' => 'required',
                'deliveryAddress' => 'required',
                'amount' => 'required',
                'currency' => 'required',
                'reference' => 'required',
                'metadata' => 'required',
            ]);
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
                    foreach (Cart::instance('food')->content() as $cart) {
                        $order = new Order;
                        $order->product_name = $cart->name;
                        $order->qty = $cart->qty;
                        $order->price = $cart->price;
                        $order->reference = $request->input('reference');
                        $order->user_id = $guestId;
                        $order->payingfor = 'Food';
                        $order->status = 'Pending';
                        $save3 = $order->save();
                    }
                    if ($save3) {
                        if ($request->payment_option == "PayStack") {
                            try{
                                return Paystack::getAuthorizationUrl()->redirectNow();
                            }catch(\Exception $e) {
                                return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
                            }
                        }elseif ($request->payment_option == "Bank Transfer") {
                            $categories = Category::all();
                            return view('transfer-food')->with([
                                'firstName' => $request->input('firstName'),
                                'lastName' => $request->input('lastName'),
                                'phoneNumber' => $request->input('phoneNumber'),
                                'reference' => $request->input('reference'),
                                'email' => $request->input('email'),
                                'amount' => $request->input('amount2'),
                                'categories' => $categories,
                                'deliveryFee' => $request->deliveryFee,
                            ]);
                        }else {
                            $fullname = $request->lastName.' '.$request->firstName;
                            $res = new Client();
                            $response = $res->request('POST', 'https://sandboxapi.fincra.com/checkout/payments', [
                                'body' => '{
                                    "customer":{
                                        "name":"'.$fullname.'",
                                        "email":"'.$request->email.'",
                                        "phoneNumber":"'.$request->phoneNumber.'"
                                    },
                                    "metadata": {
                                        "payingfor":"Food",
                                        "payment_method":"Fincra"
                                    },
                                    "amount":"'.$request->amount2.'",
                                    "redirectUrl":"https://qmarthub.com/fincra/callback",
                                    "currency":"'.$request->currency.'",
                                    "reference":"'.$request->reference.'",
                                    "feeBearer":"customer",
                                    "settlementDestination":"wallet"
                                }',
                                'headers' => [
                                'accept' => 'application/json',
                                'api-key' => 'D4CzuyiC8nwutsFGEKVwuNeFuWSPFHOo',
                                'content-type' => 'application/json',
                                'x-business-id' => '63f4cf1bca322c92e6ba04f4',
                                'x-pub-key' => 'pk_test_NjNmNGNmMWNjYTMyMmM3ZjVlYmEwNGY4OjoxMjY2OTk=',
                                ],
                            ]);
                            $callback = json_decode($response->getBody(), true);
                            return redirect($callback['data']['link']);
                        }
                    }
                }
            }
        }
    }

    public function handleGatewayCallback(Request $request)
    {
       $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
       if(!$reference){
            dd('No reference supplied');
        }else{
            $res = new Client();
            $response = $res->request('GET', 'https://sandboxapi.fincra.com/checkout/payments/merchant-reference/'.$reference, [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => 'D4CzuyiC8nwutsFGEKVwuNeFuWSPFHOo',
                    'x-business-id' => '63f4cf1cca322c7f5eba04f8',
                ],
                ]);

                $callback = json_decode($response->getBody(), true);
                $payingfor = $callback['data']['metadata']['payingfor'];
                $status = $callback['status'];
                if ($payingfor == 'Product') {
                    $userDetail = UserDetail::where('reference',$reference)->first();
                    $DBreference = $userDetail->reference;
                    if ($reference == $DBreference && $status == true) {
                        $mailContent = [
                            'recipient' => 'info@qmarthub.com',
                            'subject' => 'Order Request For Grocery',
                            'fullname' => $callback['data']['customer']['name'],
                            'email' => $callback['data']['customer']['email'],
                            'phoneNumber' => $callback['data']['customer']['phoneNumber'],
                            'reference' => $reference,
                            'amount' => $callback['data']['amount'],
                            'orders' => Cart::content(),
                        ];
                        \Mail::send('mail-template.order-email-template',$mailContent,function($message) use ($mailContent){
                            $message->to($mailContent['recipient'])
                                    ->from($mailContent['email'],$mailContent['fullname'])
                                    ->subject($mailContent['subject']);
                        });
                        Cart::destroy();
                        $categories = Category::all();
                        $userDetail = UserDetail::where('reference',$reference)->update(['status'=>1]);
                        return view('thanks')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }else{
                        return view('failed')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }
                }elseif ($payingfor == 'Food') {
                    $userDetail = UserDetail::where('reference',$reference)->first();
                    $DBreference = $userDetail->reference;
                    if ($reference == $DBreference && $status == true) {
                        $mailContent = [
                            'recipient' => 'info@qmarthub.com',
                            'subject' => 'Order Request For Food',
                            'fullname' => $callback['data']['customer']['name'],
                            'email' => $callback['data']['customer']['email'],
                            'phoneNumber' => $callback['data']['customer']['phoneNumber'],
                            'reference' => $reference,
                            'amount' => $callback['data']['amount'],
                        ];
                        \Mail::send('mail-template.food-order-email-template',$mailContent,function($message) use ($mailContent){
                            $message->to($mailContent['recipient'])
                                    ->from($mailContent['email'],$mailContent['fullname'])
                                    ->subject($mailContent['subject']);
                        });
                        Cart::instance('food')->destroy();
                        $categories = Category::all();
                        $userDetail = UserDetail::where('reference',$reference)->update(['status'=>1]);
                        return view('thanks')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }else{
                        return view('failed')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }
                }
        }

    }

     public function handlePaystackGatewayCallback(Request $request)
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

            $payingfor = $callback->data->metadata->payingfor;
            $status = $callback->data->status;
            if ($payingfor == 'Product') {
                    $userDetail = UserDetail::where('reference',$reference)->first();
                    $DBreference = $userDetail->reference;
                    $firstName = $userDetail->first_name;
                    $lastName = $userDetail->last_name;
                    $fullname = $lastName." ".$firstName;
                    $email = $userDetail->email;
                    $guestDetail = GuestUser::where('email',$email)->first();
                    $phoneNumber = $guestDetail->phone;
                    if ($reference == $DBreference && $status == 'success') {
                        $mailContent = [
                            'recipient' => 'info@qmarthub.com',
                            'subject' => 'Order Request For Grocery',
                            'fullname' => $fullname,
                            'email' => $email,
                            'phoneNumber' => $phoneNumber,
                            'reference' => $reference,
                            'amount' => $callback->data->amount/100,
                            'orders' => Cart::content(),
                        ];
                        \Mail::send('mail-template.order-email-template',$mailContent,function($message) use ($mailContent){
                            $message->to($mailContent['recipient'])
                                    ->from($mailContent['email'],$mailContent['fullname'])
                                    ->subject($mailContent['subject']);
                        });
                        Cart::destroy();
                        $categories = Category::all();
                        $userDetail = UserDetail::where('reference',$reference)->update(['status'=>1]);
                        return view('thanks')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }else{
                        return view('failed')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }
                }elseif ($payingfor == 'Food') {
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
                        $mailContent = [
                            'recipient' => 'info@qmarthub.com',
                            'subject' => 'Order Request For Food',
                            'fullname' => $fullname,
                            'email' => $email,
                            'phoneNumber' => $phoneNumber,
                            'reference' => $reference,
                            'amount' => $callback->data->amount/100,
                        ];
                        \Mail::send('mail-template.food-order-email-template',$mailContent,function($message) use ($mailContent){
                            $message->to($mailContent['recipient'])
                                    ->from($mailContent['email'],$mailContent['fullname'])
                                    ->subject($mailContent['subject']);
                        });
                        Cart::instance('food')->destroy();
                        $categories = Category::all();
                        $userDetail = UserDetail::where('reference',$reference)->update(['status'=>1]);
                        return view('thanks')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }else{
                        return view('failed')->with(['paymentDetails' =>$callback,'categories' => $categories]);
                    }
                }

        }

    }

    public function uploadProductReceipt(Request $request)
    {
        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNumber' => 'required',
            'reference' => 'required',
            'email' => 'required',
            'amount' => 'required',
        ]);

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
        $receipt->save();
        $mailContent = [
            'recipient' => 'info@qmarthub.com',
            'subject' => 'Order Request For Grocery',
            'fullname' => $request->input('firstName')." ".$request->input('lastName'),
            'email' => $request->input('email'),
            'phoneNumber' => $request->input('phoneNumber'),
            'reference' => $request->input('reference'),
            'amount' => $request->input('amount'),
            'orders' => Cart::content(),
        ];
        \Mail::send('mail-template.order-email-template',$mailContent,function($message) use ($mailContent){
            $message->to($mailContent['recipient'])
                    ->from($mailContent['email'],$mailContent['fullname'])
                    ->subject($mailContent['subject']);
        });
        $categories = Category::all();
        Cart::destroy();
        return view('thanks')->with(['categories' => $categories]);
    }

    public function uploadFoodReceipt(Request $request)
    {
        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNumber' => 'required',
            'reference' => 'required',
            'email' => 'required',
            'amount' => 'required',
        ]);

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
        $receipt->save();
        $mailContent = [
            'recipient' => 'info@qmarthub.com',
            'subject' => 'Order Request For Food',
            'fullname' => $request->input('firstName')." ".$request->input('lastName'),
            'email' => $request->input('email'),
            'phoneNumber' => $request->input('phoneNumber'),
            'reference' => $request->input('reference'),
            'amount' => $request->input('amount'),
        ];
        \Mail::send('mail-template.food-order-email-template',$mailContent,function($message) use ($mailContent){
            $message->to($mailContent['recipient'])
                    ->from($mailContent['email'],$mailContent['fullname'])
                    ->subject($mailContent['subject']);
        });
        $categories = Category::all();
        Cart::instance('food')->destroy();
        return view('thanks')->with(['categories' => $categories]);
    }

}
