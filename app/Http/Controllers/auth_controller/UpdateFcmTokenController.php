<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateFcmTokenController extends Controller
{
    use MyResponseTrait;
    public function updateFcmToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $user = $request->user();
        $res = User::where('id', $user->id)
            ->update([
                'fcm_token' => $request->token,
            ]);
        if ($res != 0) {
            $user->fcm_token = $request->token;
            return $this->get_response($user, 200, "update setting completed completed");
        } else {
            return $this->get_error_response(401, 'you have not setting to update!');
        }
    }
}