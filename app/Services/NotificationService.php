<?php

namespace App\Services;
use App\Models\constant\Constant;
use App\Models\Follow;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Log;

class NotificationService 
{
    public function sendNotification($ads , $user_id , $update = false)
    {

        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = $update ? Constant::UPDATE_ADS_NOTIFICATION_TYPE : Constant::ADS_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $ads,
        ];
        $users = null ;
        if($update){
            $users = UserSetting::get();
        }else {
            $users = UserSetting::where(function ($query) use ($user_id) {
                $query->where('user_id', '!=', $user_id)
                    ->orWhereNull('user_id');
            })->get();
        }
        $tokens = $users->filter(function ($user)  use ($user_id , $ads) {
            if($user->user_id == $user_id)return true ;
            if ($user->fcm_token == null) {
                return false;
            }
            if($ads->admin == true){
                if($user->notification_type[5] == '1')
                    return true ;
                return false;
            }
            else if($ads->ads_type == Constant::POST_ADS_TYPE && $user->notification_type[1] == '1'){
                return true ;
            }
            else if($ads->ads_type == Constant::SERVICE_ADS_TYPE && $user->notification_type[2] == '1'){
                return true ;
            }
            else if($ads->ads_type == Constant::NORMAL_ADS_TYPE && $user->notification_type[3] == '1'){
                return true ;
            }
            else if($user->notification_type[4] == '1'){
                $followRes = Follow::where("follower_id", "=", $user->user_id)->where("followed_id", "=", $user_id)->first();
                return $followRes != null;
            }
            return false;
        })->pluck('fcm_token')->unique()->toArray();
        $data = [
            "registration_ids" => $tokens,
            "priority" => "high",
            'data' => $extraData,
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            // $error = curl_error($ch);
        }
        // Close cURL
        curl_close($ch);
    }
    public function sendCommentNotificationToOneUser($comment , $user)
    {
        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::COMMENT_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $comment,
        ];
        $tokens = [$user->fcm_token];
        $data = [
            "registration_ids" => $tokens,
            'data' => $extraData,
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            // $error = curl_error($ch);
        }
        // Close cURL
        curl_close($ch);
    }
    public function sendFeedbackNotificationToOneUser($feedback,$user,$description)
    {
        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::FEED_BACK_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $feedback,
        ];
        $tokens = [$user->fcm_token];

        $data = [
            "registration_ids" => $tokens,
            "priority" => "high",
            'data' => $extraData,
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            // $error = curl_error($ch);
        }
        // Close cURL
        curl_close($ch);
    }
}