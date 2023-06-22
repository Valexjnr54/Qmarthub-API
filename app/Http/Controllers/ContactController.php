<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Product;
use App\Category;
use App\Brand;
use App\Food;
use App\FoodVendor;
use DB;

class ContactController extends Controller
{
    public function sendMail(Request $request)
    {
        $this->validate($request,[
            'fullname' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);
        
        $mailData = [
            'recipient' => 'info@qmarthub.com',
            'fromEmail' => $request->email,
            'name' => $request->fullname,
            'subject' => $request->subject,
            'body' => $request->message,
        ];
        \Mail::send('mail-template.contact-email-template',$mailData,function($message) use ($mailData){
            $message->to($mailData['recipient'])
                    ->from($mailData['fromEmail'],$mailData['name'])
                    ->subject($mailData['subject']);
        });
        return back()->with(['success' => 'Your message Has been Sent, your message will be reviewed!','title'=>'E-mail Sent']);
    }
}