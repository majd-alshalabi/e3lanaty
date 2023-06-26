<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSettingController extends Controller
{
    use MyResponseTrait;

    public function updateNotificationType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_type' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        $res = UserSetting::where('user_id', $user->id)
            ->update([
                'notification_type' => $request->notification_type,
            ]);
        if($res != 0){
            $user_setting = UserSetting::where('user_id', $user->id)->first();
            return $this->get_response($user_setting, 200, "update setting completed completed");
        }
        else 
        {
            return $this->get_error_response(401, 'you have not setting to update!');
        }
    }
    public function uploadImage(Request $request)
    {

        $imageName = '';
    
        if ($request->image != null) {
            $imageName = "public/images/" . time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $user = $request->user();
            $user->image = $imageName;
            $user->update();
        }else {
            return $this->get_error_response(401, "enter file to upload");
        }
        
        return $this->get_response($imageName, 200, "add completed");
    }
    public function updateName(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $user = $request->user();
        $user->name = $request->name ;
        $user->update();
        
        return $this->get_response($user, 200, "update completed");
    }
}