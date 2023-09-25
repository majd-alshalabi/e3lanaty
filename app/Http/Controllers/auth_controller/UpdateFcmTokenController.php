<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateFcmTokenController extends Controller
{
    use MyResponseTrait;
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'unique_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $currentUser = auth('sanctum')->user();
        DB::beginTransaction();

        try {
            $userSetting = UserSetting::where('unique_key', $request->unique_key)->first();
            $notification_type = 111111;
            if ($userSetting != null) {
                $notification_type = $userSetting->notification_type;
                $userSetting->delete();
            }
            UserSetting::create([
                'fcm_token' => $request->token,
                'unique_key' => $request->unique_key,
                'user_id' => $currentUser != null ? $currentUser->id : null,
                'notification_type' => $notification_type,
            ]);
            DB::commit();
            return $this->get_response($request->token, 200, "update setting completed");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->get_error_response(401, 'you have not setting to update!');
        }
    }
}
