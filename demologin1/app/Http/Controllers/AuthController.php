<?php

namespace App\Http\Controllers;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Xử lý đăng nhập.
     */
    public function login(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Lấy thông tin đăng nhập
        $credentials = $request->only('email', 'password');

        // Kiểm tra xem email có tồn tại trong CSDL không
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Tài khoản không tồn tại'], 401);
        }

        // So sánh mật khẩu
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Mật khẩu không đúng'], 401);
        }

      
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json(['error' => 'Đăng nhập không thành công'], 500);
        }

        $user->update(['token' => $token]);
        $cookie = Cookie::make('token', $token, 60);

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
        ])->cookie($cookie);
    }

    public function checkAuth(Request $request)
    {
    
        $token = $request->cookie('token');

        if(!$token)
        {
            return response()->json(['error'=> 'khong tim thay token'],401);
        }
        // Kiểm tra token trong database
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Xác thực thất bại'], 401);
        }

        return response()->json([
            'message' => 'Xác thực thành công',
            'user' => $user,
        ]);
    }


}
