<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class LoginController extends Controller
{
    use MyResponseTrait;
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        if (Auth::attempt($credentials)) {
            // he is a real user
            $user = $request->user();

            $userSetting = UserSetting::where('id', '=', $user->id)->first();
            $user->user_setting = $userSetting;

            $token = $user->createToken('authToken');

            return $this->get_response_for_login($user, 200, "login completed", $token->plainTextToken);
        }
        return $this->get_error_response(401, "enter valid email and password");
    }

    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            // Admin user authenticated successfully
            $user = Auth::guard('admin')->user();
            $token = $user->createToken('authToken')->plainTextToken;

            return $this->get_response_for_login($user, 200, "Login completed", $token);
        }

        return $this->get_error_response(401, "Enter valid email and password");
    }

    public function logoutAdmin(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        if ($request->user('sanctum')) {
            return $this->get_response_with_only_message_and_status(200, "logout completed");
        }
        return $this->get_response_with_only_message_and_status(400, "error while logging out");
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        if ($request->user('sanctum')) {
            return $this->get_response_with_only_message_and_status(200, "logout completed");
        }
        return $this->get_response_with_only_message_and_status(400, "error while loging out");

    }
}