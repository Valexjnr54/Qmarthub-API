<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCharge;
use App\Models\CustomerReferral;
use App\Models\FoodVendor;
use App\Models\Order;
use App\Models\OrderDrink;
use App\Models\OrderExtra;
use App\Models\ReceiptUpload;
use App\Models\UserDetail;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Darryldecode\Cart\Validators\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderMail;
use App\Mail\AdminOrderMail;

class CheckoutController extends Controller
{
    use HttpResponses;
    public function getPaystackLink(Request $request)
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
            'products' =>"array",
            'foods' =>"array",
            'deliveryFee' => 'required'
        ]);
        $products = $request->input('products');
        $foods = $request->input('foods');
        if (auth()->user()) {
            $userDetail = new CustomerCharge;
            $userDetail->customer_id = auth()->user()->id;
            $userDetail->reference = $request->input('reference');
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->service_charge = $request->input('serviceCharge');
            $userDetail->amount = $request->input('amount');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                if (!empty($products)) {
                    foreach($products as $order)
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
                            $orders->product_image = $order['image'];
                            $orders->day = $day;
                            $orders->month = $month;
                            $orders->year = $year;
                            $orders->save();
                        } else {
                            echo "Not an array.\n";
                        }
                    }
                }
                if (!empty($foods)) {
                    foreach($foods as $order)
                    {
                        if (is_array($order)) {
                            $vendor = FoodVendor::find($order['vendorId']);
                            $vendor_name = $vendor->name;
                            $orders = new Order;
                            $orders->product_name = $order['product_name'];
                            $orders->qty = $order['qty'];
                            $orders->price = $order['price'];
                            $orders->reference = $request->input('reference');
                            $orders->payingfor = 'Food';
                            $orders->status = 'Pending';
                            $orders->vendor_id = $order['vendorId'];
                            $orders->vendor_name = $vendor_name;
                            $orders->customer_id = auth()->user()->id;
                            $orders->product_image = $order['image'];
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
                if ($request->payment_method  == "PayStack")
                {
                    $curl = curl_init();
                    $email = $request->input('email');
                    $tot = $request->input('amount');
                    $total = $tot * 100;
                    $amount = $total;
                    $reference = $request->input('reference');
                    $fullname = $request->name;

                    // url to go to after payment
                    $callback_url = 'http://127.0.0.1:8000/api/v2/checkout/paystack-callback';

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
            $userDetail->amount = $request->input('amount');
            $userDetail->status = 0;
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->service_charge = $request->input('serviceCharge');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                if (!empty($products)) {
                    foreach($products as $order)
                    {
                        if (is_array($order)) {
                            $orders = new Order;
                            $orders->product_name = $order['product_name'];
                            $orders->qty = $order['qty'];
                            $orders->price = $order['price'];
                            $orders->reference = $request->input('reference');
                            $orders->payingfor = 'Product';
                            $orders->product_image = $order['image'];
                            $orders->status = 'Pending';
                            $orders->day = $day;
                            $orders->month = $month;
                            $orders->year = $year;
                            $orders->save();
                        } else {
                            echo "Not an array.\n";
                        }
                    }
                }
                if (!empty($foods)) {
                    foreach($foods as $order)
                    {
                        if (is_array($order)) {
                            $vendor = FoodVendor::find($order['vendorId']);
                            $vendor_name = $vendor->name;
                            $orders = new Order;
                            $orders->product_name = $order['product_name'];
                            $orders->qty = $order['qty'];
                            $orders->price = $order['price'];
                            $orders->reference = $request->input('reference');
                            $orders->payingfor = 'Food';
                            $orders->status = 'Pending';
                            $orders->vendor_id = $order['vendorId'];
                            $orders->product_image = $order['image'];
                            $orders->vendor_name = $vendor_name;
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
                if ($request->payment_method  == "PayStack")
                {
                    $curl = curl_init();
                    $email = $request->input('email');
                    $tot = $request->input('amount');
                    $total = $tot * 100;
                    $amount = $total;
                    $reference = $request->input('reference');
                    $fullname = $request->name;

                    // url to go to after payment
                    $callback_url = 'http://127.0.0.1:8000/api/v2/checkout/paystack-callback';

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
        'products' =>"array",
        'foods' =>"array",
        'receipt' => 'required|mimes:jpeg,jpg,pdf,png|max:5120',
        'deliveryFee' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        if (auth()->user()) {
            $userDetail = new CustomerCharge;
            $userDetail->customer_id = auth()->user()->id;
            $userDetail->reference = $request->input('reference');
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->service_charge = $request->input('serviceCharge');
            $userDetail->amount = $request->input('amount');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                if (!empty($products)) {
                    foreach($products as $order)
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
                            $orders->product_image = $order['image'];
                            $orders->day = $day;
                            $orders->month = $month;
                            $orders->year = $year;
                            $orders->save();
                        } else {
                            echo "Not an array.\n";
                        }
                    }
                }
                if (!empty($foods)) {
                    foreach($foods as $order)
                    {
                        if (is_array($order)) {
                            $vendor = FoodVendor::find($order['vendorId']);
                            $vendor_name = $vendor->name;
                            $orders = new Order;
                            $orders->product_name = $order['product_name'];
                            $orders->qty = $order['qty'];
                            $orders->price = $order['price'];
                            $orders->reference = $request->input('reference');
                            $orders->payingfor = 'Food';
                            $orders->status = 'Pending';
                            $orders->vendor_id = $order['vendorId'];
                            $orders->vendor_name = $vendor_name;
                            $orders->customer_id = auth()->user()->id;
                            $orders->product_image = $order['image'];
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
                    $charge = CustomerCharge::where(['reference' => $request->input('reference'), 'customer_id' => auth()->user()->id])->first();
                    $customer = Customer::where(['id' => auth()->user()->id])->first();
                    $orders = Order::where(['reference' => $request->input('reference'), 'customer_id' => auth()->user()->id])->get();
                    $drinks = OrderDrink::where('reference',$request->input('reference'))->get();
                    $extras = OrderExtra::where('reference',$request->input('reference'))->get();
                    $fro = 'info@qmarthub.com';
                    $subject = 'Order Details';
                    $view = 'mail-template.order';
                    $view2 = 'mail-template.admin-order';
                    $data = [
                        'fullname' => $customer->name,
                        'email' => $customer->email,
                        'location' => $charge->location,
                        'phone' => $customer->phone_number,
                        'reference' => $charge->reference,
                        'amount' => $charge->amount,
                        'orders' => $orders,
                        'drinks' => $drinks,
                        'extras' => $extras,
                    ];
                    $send = Mail::to($customer->email)->send(new OrderMail($fro, $subject, $view, $data));

                    if ($send) {
                        $send = Mail::to('info@qmarthub.com')->send(new AdminOrderMail($customer->email, $subject, $view2, $data));
                    }
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
            $userDetail->amount = $request->input('amount');
            $userDetail->status = 0;
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->service_charge = $request->input('serviceCharge');
            $save = $userDetail->save();
            if ($save) {
                $myDate = date("Y-m-d");
                $day = Carbon::createFromFormat('Y-m-d', $myDate)->format('d');
                $month = Carbon::createFromFormat('Y-m-d', $myDate)->format('m');
                $year = Carbon::createFromFormat('Y-m-d', $myDate)->format('Y');
                if (!empty($products)) {
                    foreach($products as $order)
                    {
                        if (is_array($order)) {
                            $orders = new Order;
                            $orders->product_name = $order['product_name'];
                            $orders->qty = $order['qty'];
                            $orders->price = $order['price'];
                            $orders->reference = $request->input('reference');
                            $orders->payingfor = 'Product';
                            $orders->status = 'Pending';
                            $orders->product_image = $order['image'];
                            $orders->day = $day;
                            $orders->month = $month;
                            $orders->year = $year;
                            $orders->save();
                        } else {
                            echo "Not an array.\n";
                        }
                    }
                }
                if (!empty($foods)) {
                    foreach($foods as $order)
                    {
                        if (is_array($order)) {
                            $vendor = FoodVendor::find($order['vendorId']);
                            $vendor_name = $vendor->name;
                            $orders = new Order;
                            $orders->product_name = $order['product_name'];
                            $orders->qty = $order['qty'];
                            $orders->price = $order['price'];
                            $orders->reference = $request->input('reference');
                            $orders->payingfor = 'Food';
                            $orders->status = 'Pending';
                            $orders->vendor_id = $order['vendorId'];
                            $orders->vendor_name = $vendor_name;
                            $orders->product_image = $order['image'];
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
                    $detail = UserDetail::where('reference',$request->input('reference'))->first();
                    $orders = Order::where('reference',$request->input('reference'))->get();
                    $drinks = OrderDrink::where('reference',$request->input('reference'))->get();
                    $extras = OrderExtra::where('reference',$request->input('reference'))->get();
                    $fro = 'info@qmarthub.com';
                    $subject = 'Order Details';
                    $view = 'mail-template.order';
                    $view2 = 'mail-template.admin-order';
                    $data = [
                        'fullname' => $detail->name,
                        'email' => $detail->email,
                        'location' => $detail->location,
                        'phone' => $detail->phone,
                        'reference' => $detail->reference,
                        'amount' => $detail->amount,
                        'orders' => $orders,
                        'drinks' => $drinks,
                        'extras' => $extras,
                    ];



                    $send = Mail::to($detail->email)->send(new OrderMail($fro, $subject, $view, $data));

                    if ($send) {
                        $send = Mail::to('info@qmarthub.com')->send(new AdminOrderMail($detail->email, $subject, $view2, $data));
                    }
                    return $this->success('','Receipt Has been uploaded Successfully',200);
                } else {
                    return $this->error('','Internal Server Error',500);
                }
            } else {
                return $this->error('','Internal Server Error',500);
            }

        }
    }

    public function paystackCallback()
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
                // "authorization: Bearer sk_live_fb184e420d3304967b4ff2522e12c1bc775ddba1",
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

            if(auth()->user()){
                $customerDetail = CustomerCharge::where(['reference' => $reference, 'customer_id' => auth()->user()->id])->first();
                $DBreference = $customerDetail->reference;
                if ($reference == $DBreference && $status == true) {
                    $userDetail = CustomerCharge::where(['reference' => $reference, 'customer_id' => auth()->user()->id])->update(['status'=>1]);
                    $charge = CustomerCharge::where(['reference' => $reference, 'customer_id' => auth()->user()->id])->first();
                    $customer = Customer::where(['id' => auth()->user()->id])->first();
                    $orders = Order::where(['reference' => $reference, 'customer_id' => auth()->user()->id])->get();
                    $drinks = OrderDrink::where('reference',$reference)->get();
                    $extras = OrderExtra::where('reference',$reference)->get();
                    $fro = 'info@qmarthub.com';
                    $subject = 'Order Details';
                    $view = 'mail-template.order';
                    $view2 = 'mail-template.admin-order';
                    $data = [
                        'fullname' => $customer->name,
                        'email' => $customer->email,
                        'location' => $charge->location,
                        'phone' => $customer->phone_number,
                        'reference' => $charge->reference,
                        'amount' => $charge->amount,
                        'orders' => $orders,
                        'drinks' => $drinks,
                        'extras' => $extras,
                    ];
                    $send = Mail::to($customer->email)->send(new OrderMail($fro, $subject, $view, $data));

                    if ($send) {
                        $send = Mail::to('info@qmarthub.com')->send(new AdminOrderMail($customer->email, $subject, $view2, $data));
                    }
                    // return response()->json(['message'=>'Payment Have been Confirmed','url'=>'http://127.0.0.1:8000/order-summary?trxref='.$callback->data->reference.'&reference='.$callback->data->reference],200);
                    return redirect("http://127.0.0.1:8000/order-summary?trxref=".$callback->data->reference."&reference=".$callback->data->reference."");
                }else{
                    return $this->error('','Failed to confirm Payment',401);
                }
            }else{
                $userDetail = UserDetail::where('reference',$reference)->first();
                $DBreference = $userDetail->reference;
                if ($reference == $DBreference && $status == true) {
                    $userDetail = UserDetail::where('reference',$reference)->update(['status'=>1]);
                    $detail = UserDetail::where('reference',$reference)->first();
                    $orders = Order::where('reference',$reference)->get();
                    $drinks = OrderDrink::where('reference',$reference)->get();
                    $extras = OrderExtra::where('reference',$reference)->get();
                    $fro = 'info@qmarthub.com';
                    $subject = 'Order Details';
                    $view = 'mail-template.order';
                    $view2 = 'mail-template.admin-order';
                    $data = [
                        'fullname' => $detail->name,
                        'email' => $detail->email,
                        'location' => $detail->location,
                        'phone' => $detail->phone,
                        'reference' => $detail->reference,
                        'amount' => $detail->amount,
                        'orders' => $orders,
                        'drinks' => $drinks,
                        'extras' => $extras,
                    ];



                    $send = Mail::to($detail->email)->send(new OrderMail($fro, $subject, $view, $data));

                    if ($send) {
                        $send = Mail::to('info@qmarthub.com')->send(new AdminOrderMail($detail->email, $subject, $view2, $data));
                    }
                    // return response()->json(['message'=>'Payment Have been Confirmed','url'=>'http://127.0.0.1:8000/order-summary?trxref='.$callback->data->reference.'&reference='.$callback->data->reference],200);
                    return redirect("http://127.0.0.1:8000/order-summary?trxref=".$callback->data->reference."&reference=".$callback->data->reference."");
                }else{
                    return $this->error('','Failed to confirm Payment',401);
                }
            }
        }

    }
}
