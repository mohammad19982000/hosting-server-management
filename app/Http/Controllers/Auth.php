<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Auth extends Controller
{
    // This method is called only by new customers, so the role attribute will be always 'Customer'.
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'company_name' => 'required|string|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed',
                                     'message' => $validator->errors()]);
        }

        $user = User::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => "Customer"

        ]);

        $customer = customer::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['status' => 'success',
                    'message' => 'User registered successfully',
                    'access_token' => $token, 'token_type' => 'Bearer']);
    }



    public function login(Request $request)
    {
        if (!\Illuminate\Support\Facades\Auth::attempt($request->only('email', 'password'))) {
            return response()
                ->json(['status' => 'failed','message' => 'incorrect email or password']);
        }


        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['status' => 'success',
                    'message' => 'User logged in successfully',
                    'access_token' => $token, 'token_type' => 'Bearer', 'role' => $user->role]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]
        );
    }
}
