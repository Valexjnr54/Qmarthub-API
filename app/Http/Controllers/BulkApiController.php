<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use App\Models\ReceiptUpload;
use App\Models\Product;
use App\Models\Category;
use App\Models\Bulk;
use App\Models\BulkUserDetail;
use App\Models\BulkOrder;
use Paystack;

class BulkApiController extends Controller
{
   public function getBulkItems()
   {
       $bulks = Bulk::all();
        return $bulks;
   }
   public function getBulkItem($id)
   {
       $bulks = Bulk::find($id);
        return $bulks;
   }
   public function getProducts()
   {
       $products = Product::all();
        return $products;
   }
   public function getProduct($id)
   {
       $product = Product::find($id);
        return $product;
   }
   public function searchProducts(Request $request)
   {
        $this->validate($request,[
            'query' => 'required',
        ]);
        $query = $request->input('query');
        $productsearch = Product::where('product_name', 'like', "%$query%")
                           ->orWhere('content', 'like', "%$query%")
                           ->orWhere('description', 'like', "%$query%")
                           ->get();
        return response()->json($productsearch);
   }

   public function createOrder(Request $request)
   {

       $this->validate($request,[
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required',
                'phoneNumber' => 'required',
                'deliveryAddress' => 'required',
                'totalAmount' => 'required',
                'currency' => 'required',
                'reference' => 'required',
                'metadata' => 'required',
                'payment_method' => 'required',
                'data' =>"required|array",
                'deliveryFee' => 'required',
                'discount' => 'required'
            ]);
             $data = $request->input('data');
            $userDetail = new BulkUserDetail;
            $userDetail->first_name = $request->input('firstName');
            $userDetail->last_name = $request->input('lastName');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->email = $request->input('email');
            $userDetail->reference = $request->input('reference');
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->discount = $request->input('discount');
            $userDetail->status = 0;
            $save = $userDetail->save();
            if($save)
            {
                foreach($data as $order)
                 {
                     if (is_array($order)) {
                        if(BulkOrder::Create($order)){
                            $save3 = true;
                        }
                    } else {
                        echo "Not an array.\n";
                    }
                 }
                if($save3)
                {
                    if($request->payment_method  == "PayStack")
                    {
                        $curl = curl_init();
                        $email = $request->input('email');
                        $tot = $request->input('totalAmount');
                        $total = $tot * 100;
                        $amount = $total;
                        $reference = $request->input('reference');
                        $fullname = $request->lastName.' '.$request->firstName;

                        // url to go to after payment
                        $callback_url = 'https://www.qmarthub.com/api/v1/paystack/callback';

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
                            'metadata'=> $request->metadata,
                          ]),
                          CURLOPT_HTTPHEADER => [
                            "authorization: Bearer sk_live_fb184e420d3304967b4ff2522e12c1bc775ddba1", //replace this with your own test key
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
                        // try{
                        //     return Paystack::getAuthorizationUrl()->redirectNow();
                        // }catch(\Exception $e) {
                        //     return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
                        // }
                    }elseif($request->payment_method  == "Fincra")
                    {
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
                                    "payingfor":"Bulk Buying for 10k",
                                    "payment_method":"Fincra"
                                },
                                "amount":"'.$request->totalAmount.'",
                                "redirectUrl":"https://qmarthub.com/api/v1/fincra/callback",
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

                        return response()->json(['response' => 'success', 'link' => $callback['data']['link']]);


                    }

                }
            }else{
                return response()->json('Something Went Wrong in save user details');
            }
   }

   public function uploadReceipt(Request $request)
   {
        $validator = Validator::make($request->all(),
                         [
                            'firstName' => 'required',
                            'lastName' => 'required',
                            'email' => 'required',
                            'phoneNumber' => 'required',
                            'deliveryAddress' => 'required',
                            'totalAmount' => 'required',
                            'currency' => 'required',
                            'reference' => 'required',
                            'metadata' => 'required',
                            'payment_method' => 'required',
                            'data' =>"required",
                            'transferReceipt' => 'required|mimes:jpeg,jpg,pdf,png|max:5120',
                            'deliveryFee' => 'required',
                            'discount' => 'required'
                         ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $data = json_decode($request->input('data'), true);
            //$data = $request->input('data');
            $userDetail = new BulkUserDetail;
            $userDetail->first_name = $request->input('firstName');
            $userDetail->last_name = $request->input('lastName');
            $userDetail->location = $request->input('deliveryAddress');
            $userDetail->email = $request->input('email');
            $userDetail->reference = $request->input('reference');
            $userDetail->delivery_fee = $request->input('deliveryFee');
            $userDetail->discount = $request->input('discount');
            $userDetail->status = 0;
            $save = $userDetail->save();
            if($save)
            {
                foreach($data as $order)
                {
                    if (is_array($order)) {
                        if(BulkOrder::Create($order)){
                            $save2 = true;
                        }
                    } else {
                        echo "Not an array.\n";
                    }
                }
                if($save2)
                {
                    if($request->hasFile('transferReceipt')){
                        $fileNameWithExt = $request->file('transferReceipt')->getClientOriginalName();
                        $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
                        $ext = $request->file('transferReceipt')->getClientOriginalExtension();
                        $fileNameToStore = $fileName.'_'.time().'.'.$ext;
                        $path = $request->file('transferReceipt')->storeAs('public/receipt/bulk_receipt_files',$fileNameToStore);
                    }
                    $receipt = new ReceiptUpload;
                    $receipt->first_name = $request->input('firstName');
                    $receipt->last_name = $request->input('lastName');
                    $receipt->email = $request->input('email');
                    $receipt->reference = $request->input('reference');
                    $receipt->phone_number = $request->input('phoneNumber');
                    $receipt->receipt = $fileNameToStore;
                    $receipt->type = 'Bulk Buy';
                    $receipt->save();
                    $orders = BulkOrder::where('reference',$request->input('reference'))->get();
                    $mailContent = [
                        'recipient' => 'info@qmarthub.com',
                        'subject' => 'Order Request For Bulk Buy Grocery',
                        'fullname' => $request->input('firstName')." ".$request->input('lastName'),
                        'email' => $request->input('email'),
                        'phoneNumber' => $request->input('phoneNumber'),
                        'reference' => $request->input('reference'),
                        'amount' => $request->input('amount'),
                        'orders' => $orders,
                    ];
                    \Mail::send('mail-template.bulk-order-email-template',$mailContent,function($message) use ($mailContent){
                        $message->to($mailContent['recipient'])
                                ->from($mailContent['email'],$mailContent['fullname'])
                                ->subject($mailContent['subject']);
                    });
                    return response()->json(['response' => 'success' , 'message' => 'Your Receipt have been uploaded and will be review shortly.']);
                }
            }else{
                return response()->json('Something Went Wrong in save user details');
            }
   }

   public function callback(Request $request)
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
            $userDetail = BulkUserDetail::where('reference',$reference)->first();
            $DBreference = $userDetail->reference;
            if ($reference == $DBreference && $status == true) {
                $categories = Category::all();
                $userDetail = BulkUserDetail::where('reference',$reference)->update(['status'=>1]);
                return view('bulk.thanks')->with(['paymentDetails' =>$callback,'categories' => $categories]);
            }else{
                return view('bulk.failed')->with(['paymentDetails' =>$callback,'categories' => $categories]);
            }
        }

   }

   public function paystackCallback(Request $request)
   {
        //$reference = $request->reference;
        $curl = curl_init();
        $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
        if(!$reference){
          die('No reference supplied');
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer sk_live_fb184e420d3304967b4ff2522e12c1bc775ddba1",
            "cache-control: no-cache"
          ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if($err){
            // there was an error contacting the Paystack API
          die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response);

        if(!$tranx->status){
          // there was an error from the API
          die('API returned error: ' . $tranx->message);
        }

        $status = $tranx->data->status; // Getting the status of the transaction
        $amount = $tranx->data->amount;
        $paystackReference = $tranx->data->reference;
        //$payingFor = $tranx->data->metadata->payingfor;
        $categories = Category::all();

        $bulkUserDetail = BulkUserDetail::where('reference',$paystackReference)->first();
        $reference = $bulkUserDetail->reference;
        if ($reference == $paystackReference && $status == 'success') {
            $userDetail = BulkUserDetail::where('reference',$reference)->update(['status'=>1]);
            return view('bulk.thanks')->with([
                    'categories' => $categories,
                    ]);
        }else{
            return view('bulk.failed')->with([
                    'categories' => $categories,
                    ]);
        }

   }
}
