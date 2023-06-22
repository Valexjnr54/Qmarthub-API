<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReceiptUpload;
use App\Models\UserDetail;
use App\Models\BulkUserDetail;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }
    public function index()
    {
        $receipts = ReceiptUpload::where('type','Single Buy')->get();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.receipts')->with(['receipts'=>$receipts]);
    }

    public function bulkIndex()
    {
        $receipts = ReceiptUpload::where('type','Bulk Buy')->get();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.receipts')->with(['receipts'=>$receipts]);
    }

    public function confirmReceipt($id)
    {
        $receipt = ReceiptUpload::find($id);
        $reference = $receipt->reference;
        $receiptUpdate = ReceiptUpload::where('id',$id)->update(['status'=>1]);
        $type = $receipt->type;
        if($type == 'Single Buy'){
            $userDetail = UserDetail::where('reference',$reference)->first();
            $userReference = $userDetail->reference;
            $userUpdate = UserDetail::where('reference',$userReference)->update(['status'=>1]);
        }elseif($type == 'Bulk Buy'){
            $userDetail = BulkUserDetail::where('reference',$reference)->first();
            $userReference = $userDetail->reference;
            $userUpdate = UserDetail::where('reference',$userReference)->update(['status'=>1]);
        }
        return back()->with(['success' => 'Payment Confirmed!','title'=>'Confirm Payment']);
    }

    public function deleteReceipt($id)
    {
        $receipt = ReceiptUpload::find($id);
        $receipt->delete();
        return back()->with(['success'=>'Receipt has been deleted Successfully','title'=>'Delete Receipt']);
    }

    public function loadModal($id)
    {
        $receipt = ReceiptUpload::find($id);

      $html = "";
      if(!empty($receipt)){
        $receiptFile = $receipt->receipt;
        $split = explode('.',$receiptFile);
        $ext = $split[1];
        if($receipt->type == 'Single Buy')
        {
            if ($ext == 'jpg' || $ext== 'jpeg' || $ext == 'png' || $ext=='gif' || $ext == 'JPG' || $ext == 'JPEG' || $ext == 'PNG' || $ext == 'GIF') {
                $show = '<img src="../../storage/receipt/receipt_files/'.$receiptFile.'" width="600px" height="600px" alt="Image Receipt">';
            } else {
                $show = '<iframe src="../../storage/receipt/receipt_files/'.$receiptFile.'" width="600px" height="600px">
                </iframe>';
            }
        }else if($receipt->type == 'Bulk Buy')
        {
            if ($ext == 'jpg' || $ext== 'jpeg' || $ext == 'png' || $ext=='gif' || $ext == 'JPG' || $ext == 'JPEG' || $ext == 'PNG' || $ext == 'GIF') {
                $show = '<img src="../../storage/receipt/bulk_receipt_files/'.$receiptFile.'" width="600px" height="600px" alt="Image Receipt">';
            } else {
                $show = '<iframe src="../../storage/receipt/bulk_receipt_files/'.$receiptFile.'" width="600px" height="600px">
                </iframe>';
            }
        }



         $html = "<tr>
              <td width='30%'><b>Last Name:</b></td>
              <td width='70%'> ".$receipt->last_name."</td>
           </tr>
           <tr>
              <td width='30%'><b>First Name:</b></td>
              <td width='70%'> ".$receipt->first_name."</td>
           </tr>
           <tr>
              <td width='30%'><b>Phone Number:</b></td>
              <td width='70%'> ".$receipt->phone_number."</td>
           </tr>
           <tr>
              <td width='30%'><b>Email:</b></td>
              <td width='70%'> ".$receipt->email."</td>
           </tr>
           <tr>
              <td width='30%'><b>Type:</b></td>
              <td width='70%'> Receipt for ".$receipt->type."</td>
           </tr>
           <tr>
              <td width='30%'><b>Reference:</b></td>
              <td width='70%'> ".$receipt->reference."</td>
           </tr>
           <tr>
                <td width='30%'><b>Receipt:</b></td>
                <td width='70%'>".$show."</td>
           </tr>
           ";
      }
      $response['html'] = $html;

      return response()->json($response);
    }
}
