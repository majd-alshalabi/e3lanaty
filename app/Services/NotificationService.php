<?php

namespace App\Services;
use App\Models\constant\Constant;
use App\Models\Follow;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Log;

class NotificationService 
{
    public function sendNotification($ads , $user_id)
    {

        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::ADS_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $ads,
        ];
        $users = UserSetting::where('user_id', '!=', $user_id)->orWhere("user_id",null)->get();
        $tokens = $users->filter(function ($user)  use ($user_id) {
            if($user->fcm_token == null){
                return false;
            }
            if($user->notification_type == Constant::ALL_USER_NOTIFICATION_TYPE)
            {
                return true ;
            }else if($user->notification_type == Constant::CANCEL_NOTIFICATION_TYPE)
            {
                return false ;
            }
            else if ($user->notification_type == Constant::FOLLOWER_NOTIFICATION_TYPE) {
                if($user->user_id == null)return false ;
                $followRes = Follow::where("follower_id", "=", $user->id)->where("followed_id", "=", $user_id)->first();
                return $followRes != null;
            }
            return true ;
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