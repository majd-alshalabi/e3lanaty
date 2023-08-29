<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            if($user->deleted){
                return $this->get_error_response(401, "this account is deleted try registering with this account");   
            }
            $token = $user->createToken('authToken');
            UserSetting::where('fcm_token' , $request->fcm_token)->update(['user_id' => $user->id]);
            return $this->get_response_for_login($user, 200, "login completed", $token->plainTextToken);
        }
        return $this->get_error_response(401, "enter valid email and password");
    }

    public function loginAdmin(Request $request)
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
            if ($user->blocked) {
                return $this->get_error_response(400, "this user is blocked because of an acceptable action");
            }
            else if (!$user->admin) {
                return $this->get_error_response(400, "un registered email");
            }

            $token = $user->createToken('authToken');

            return $this->get_response_for_login($user, 200, "login completed", $token->plainTextToken);
        }
        return $this->get_error_response(401, "enter valid email and password");
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
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->currentAccessToken()->delete();
            $user->deleted = true ;
            $user->name = "user" ;
            $user->save();

            return $this->get_response_with_only_message_and_status(200, "Account deleted successfully.");
        }

        return $this->get_response_with_only_message_and_status(400, "Error occurred while deleting the account.");
    }
}
