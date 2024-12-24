<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    //login
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token, auth()->user());
    }

    // register
    public function register(RegistrationRequest $request)
    {
        $user = User::create([
            'name' => $request->fullname,
            'email' => $request->email,
            'registration_no' => $request->registration_number,
            'password' => bcrypt($request->password),
            'contact' => $request->contact,
            'dp_url' => $request->dp_url,
        ]);
        return response()->json(['user' => $user]);
    }

    // get user details with token
    public function getUserDetails(Request $request)
    {
        $user = auth()->user();
        // return error if user is not found
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json(['user' => $user]);
    }

    // upadte user details without id and registration number
    public function updateUserDetails(Request $request)
    {
        $user = auth()->user();
        DB::table('users')->where('id', $user->id)->update([
            'name' => $request->fullname,
            'contact' => $request->contact,
            'dp_url' => $request->dp_url,
        ]);
        return response()->json(['user' => auth()->user()]);
    }

    // return jwt token
    public function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user
        ]);
    }
}
