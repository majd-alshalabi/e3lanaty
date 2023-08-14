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
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $currentUser = auth('sanctum')->user();
        DB::beginTransaction();

        try {

            if ($request->last_token != null) {
                $res = UserSetting::where('fcm_token', $request->last_token)
                    ->update([
                        'fcm_token' => $request->token,
                        'user_id' => $currentUser != null ? $currentUser->id : null,
                    ]);
                if ($res != 0) {
                    DB::commit();
                    return $this->get_response($request->token, 200, "update setting completed");
                } 
            } else {
                $res = UserSetting::where('fcm_token', $request->token)
                    ->update([
                        'fcm_token' => $request->token,
                        'user_id' => $currentUser != null ? $currentUser->id : null,
                    ]);
                if ($res == 0) {
                    UserSetting::create([
                        'fcm_token' => $request->token,
                        'user_id' => $currentUser != null ? $currentUser->id : null,
                    ]);
                }
                DB::commit();

                return $this->get_response($request->token, 200, "update setting completed");


            }
            return $this->get_error_response(401, 'you have not setting to update!');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->get_error_response(401, 'you have not setting to update!');
        }

    }
}