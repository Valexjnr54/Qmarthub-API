<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login','register','newRegister']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:11',
            'email' => 'required|email|max:255|unique:customers',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sessionKey = 'Qmarthub-'.Str::random(40);
        $refferalCode = 'QM-'.strtoupper($request->name.'-'.Str::random(6));
        $refferalId  = str_replace(' ', '-', $refferalCode);

        $user = Customer::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'temp_session_ref' => $sessionKey,
                'refferal_id' => $refferalId,
                'points' => 0
            ]
        ));
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user
        ], 201);
    }

    public function newRegister(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:11',
            'email' => 'required|email|max:255|unique:customers',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sessionKey = 'Qmarthub-'.Str::random(40);
        $refferalCode = 'QM-'.strtoupper($request->name.'-'.Str::random(6));
        $refferalId  = str_replace(' ', '-', $refferalCode);

        $user = Customer::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'temp_session_ref' => $sessionKey,
                'refferal_id' => $refferalId,
                'points' => 0
            ]
        ));
        $fro = 'info@qmarthub.com';
        $subject = 'Welcome To Qmarthub';
        $view = 'mail-template.welcome';
        $data = [
            'name' => $request->name,
            'content' => 'This is a Welcome email content from Qmarthub',
        ];

        Mail::to($request->email)->send(new WelcomeMail($fro, $subject, $view, $data));

        return response()->json(['message' => 'Email sent successfully']);

        // Generate JWT token for the user
        // $token = JWTAuth::fromUser($user);
        // return response()->json([
        //     'message' => 'Registration successful',
        //     'access_token' => $token,
        //     'token_type' => 'bearer',
        //     'expires_in' => auth()->factory()->getTTL() * 60,
        //     'user' => $user
        // ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['errors' =>'Invalid Email / Password'], 401);
        }

        return $this->createToken($token);
    }

    public function createToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'User Logged out successful'
        ]);
    }
}
