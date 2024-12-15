<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {


        $credentials = $request->only('email', 'password');

        // kiem tra thong tin xem co nhap dung khong
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // luu token vao co so du lieu
        $user = User::where('email', $request->email)->first();
        $user->token = $token; 
        $user->save();

       
        $emailPage = route('email.page', ['email' => urlencode($user->email)]); // URL mã hóa email
        return redirect()->to($emailPage)->withCookie(cookie('token', $token, 1));

    }

    public function showEmailPage($email)
    {
        return view('email', ['email' => $email]); 
    }


}
