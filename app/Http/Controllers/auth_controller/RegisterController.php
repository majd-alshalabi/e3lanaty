<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    use MyResponseTrait;


    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => [
                'required',
                'min:8',
            ],
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $tempUser = User::where("email", $request->email)->first();
        $userDeleted = false;
        if ($tempUser != null) {
            $userDeleted = $tempUser->deleted;
            if (!$userDeleted) {
                return $this->get_error_response(401, "this email is already in use");
            }
        }

        $account_type = $request->type_of_account;
        if ($account_type == null)
            $account_type = 0;
        $imageName = null;
        if ($request->image != null) {
            $imageName = "public/images/" . time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
        }
        $user = null;
        if ($userDeleted) {
            $tempUser->name = $request->name ;
            $tempUser->password = bcrypt($request->password);
            $tempUser->deleted = false;
            $tempUser->save();
            $user = $tempUser;
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'location' => $request->location,
                'password' => bcrypt($request->password),
                'image' => $imageName,
                'phone_number' => $request->phone_number,
                'about_me' => $request->about_me,
                'type_of_account' => $account_type,
            ]);
        }
        $token = $user->createToken('authToken');
        UserSetting::where('fcm_token', $request->fcm_token)->update(['user_id' => $user->id]);
        return $this->get_response_for_login($user, 200, "resgister completed", $token->plainTextToken);
    }
    public function registerAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => [
                'required',
                'min:8',
            ]
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'admin' => true,
        ]);
        $token = $user->createToken('authToken');
        return $this->get_response_for_login($user, 200, "resgister completed", $token->plainTextToken);
    }
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users,email',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        return $this->get_response([], 200, "completed");
    }
}