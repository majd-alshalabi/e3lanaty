<?php

namespace App\Services;

use App\Models\Ads;
use App\Models\AdsDescription;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdsService
{
    public function sendNotification($ads, $user_id)
    {

        $SERVER_API_KEY = Constant::SERVER_KEY;
        $ads_notification_type = Constant::ADS_NOTIFICATION_TYPE;
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $ads,
        ];
        $users = User::where('id', '!=', $user_id)->with("userSetting")->get();
        Log::info($users);
        $tokens = $users->filter(function ($user) use ($user_id) {
            if ($user->fcm_token == null) {
                return false;
            }
            if ($user->userSetting == null) {
                return true;
            }
            if ($user->userSetting->notification_type == Constant::ALL_USER_NOTIFICATION_TYPE) {
                return true;
            } else if ($user->userSetting->notification_type == Constant::FOLLOWER_NOTIFICATION_TYPE) {
                $followRes = Follow::where("follower_id", "=", $user->id)->where("followed_id", "=", $user_id)->first();
                return $followRes != null;
            }
            return true;
        })->pluck('fcm_token')->unique()->toArray();
        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $ads->user->name . " added new ads",
                "body" => $ads->description,
                "sound" => "default"
            ],
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
            curl_error($ch);
        }
        // Close cURL
        curl_close($ch);
    }


    public function getAdsData($ads, $currentUser)
    {
        $resultList = array();
        // Eager load related data for all ads at once
        $adsIds = $ads->pluck('id');
        
        $likes = Like::whereIn('ads_id', $adsIds)->get();
        $comments = Comment::whereIn('ads_id', $adsIds)->orderBy('created_at', 'desc')->paginate(Constant::NUM_OF_PAGE);

        // Get all user IDs associated with ads and comments
        $userIds = $ads->pluck('user_id')->merge($comments->pluck('user_id'))->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($ads as $item) {
            // Get ad-specific data
            $item->description = AdsDescription::where('ads_id', $item->id)->get();
            $item->comment_count = $comments->where('ads_id', $item->id)->count();
            $item->like = $likes->where('ads_id', $item->id)->count();
            if ($currentUser != null) {
                $item->isLike = $likes->where('ads_id', $item->id)->where('user_id', $currentUser->id)->isNotEmpty();
                $item->isInFavorite = Favorite::where([
                    ['ads_id', '=', $item->id],
                    ['user_id', '=', $currentUser->id]
                ])->exists();
            }
            // Get user-specific data
            $user = $users->get($item->user_id);
            $item->user = $user ?? null;

            $resultList[] = $item;

        }

        return $resultList;
    }

}