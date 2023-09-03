<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Mail\MyTestMail;
use App\Models\ResetPassword;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Verification;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            $tempUser->name = $request->name;
            $tempUser->password = bcrypt($request->password);
            $tempUser->deleted = false;
            $tempUser->save();
            $user = $tempUser;
        } else {
            // $token = $user->createToken('authToken');
            // UserSetting::where('fcm_token', $request->fcm_token)->update(['user_id' => $user->id]);
            $randomNumber = random_int(1000, 9999);
            Mail::to($request->email)->send(new MyTestMail($randomNumber));
            Verification::where("email", $request->email)->delete();
            // return $this->get_response_for_login($user, 200, "resgister completed", $token->plainTextToken);
            $user = Verification::create([
                'name' => $request->name,
                'email' => $request->email,
                'location' => $request->location,
                'password' => bcrypt($request->password),
                'image' => $imageName,
                'phone_number' => $request->phone_number,
                'about_me' => $request->about_me,
                'type_of_account' => $account_type,
                'code' => $randomNumber,
            ]);
            return $this->get_response($user, 200, "resgister completed");
        }
        if ($userDeleted) {
            $token = $user->createToken('authToken');
            UserSetting::where('fcm_token', $request->fcm_token)->update(['user_id' => $user->id]);
            return $this->get_response_for_login($user, 200, "resgister completed", $token->plainTextToken);
        }
    }

    public function verifyAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }


        $verification = Verification::where("email", $request->email)->latest()->first();
        if ($verification == null) {
            return $this->get_error_response(402, "this not valid email");
        }
        if ($verification->code != $request->code) {
            return $this->get_error_response(402, "wrong code");
        }
        $user = User::create([
            'name' => $verification->name,
            'email' => $verification->email,
            'location' => $verification->location,
            'password' => $verification->password,
            'image' => $verification->image,
            'phone_number' => $verification->phone_number,
            'about_me' => $verification->about_me,
            'type_of_account' => $verification->type_of_account,
        ]);

        $token = $user->createToken('authToken');
        UserSetting::where('fcm_token', $request->fcm_token)->update(['user_id' => $user->id]);
        Verification::where("email", $verification->email)->delete();
        return $this->get_response_for_login($user, 200, "resgister completed", $token->plainTextToken);
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $verification = Verification::where("email", $request->email)->latest()->first();
        if ($verification == null) {
            return $this->get_error_response(402, "this is not valid email");
        }

        $randomNumber = random_int(1000, 9999);
        Mail::to($request->email)->send(new MyTestMail($randomNumber));

        $verification->code = $randomNumber;
        $verification->save();
        return $this->get_response([], 200, "resend completed");

    }


    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = User::where("email", $request->email)->first();
        if ($user == null) {
            return $this->get_error_response(401, ["not valid email"]);
        }
        $randomNumber = random_int(1000, 9999);
        $email = $user->admin ? "alshalabi211@gmail.com" : $request->email ;
        Mail::to($email)->send(new MyTestMail($randomNumber));
        ResetPassword::where("email" , $request->email)->delete();
        ResetPassword::create(
            [
                'code' => $randomNumber,
                'email' => $request->email,
            ]
        );

        return $this->get_response([], 200, "check email");
    }

    public function resetPasswordVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = User::where("email", $request->email)->first();
        if ($user == null) {
            return $this->get_error_response(401, ["not valid email"]);
        }
        $resetPassword = ResetPassword::where("email" , $request->email)->first();
        if($resetPassword->code != $request->code){
            return $this->get_error_response(401, ["wrong code"]);
        }
        $user = User::where("email" , $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        $resetPassword->delete();
        return $this->get_response([], 200, "update password done");
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $user = $request->user();
        if(Hash::check($request->old_password, $user->password)){
            $user->password = bcrypt($request->password);
            $user->save();
            return $this->get_response([], 200, "update password done");            
        }
        return $this->get_error_response(401, ["password is not correct"]);
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
            'email' => 'required|string|email',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = User::where("email", $request->email)->first();
        $res = false;
        if ($user == null) {

            $res = true;
        } else if ($user->deleted) {
            $res = true;
        }
        if ($res == false) {
            return $this->get_error_response(401, "this user is already registered in the application try login");
        }
        return $this->get_response([], 200, "completed");
    }
}