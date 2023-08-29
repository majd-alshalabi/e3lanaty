<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\FeedBack;
use App\Models\Like;
use App\Models\User;
use App\Models\UserSetting;
use App\response_trait\MyResponseTrait;
use App\Services\AdsService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use MyResponseTrait;
    public function getAdminAndStaredAds(Request $request)
    {
        $type = Constant::NORMAL_ADS_TYPE;

        $ads = Ads::where(function ($query) {
                $query->where('stared', true)
                    ->orWhere('admin', true);
            })
            ->where('ads_type', $type)
            ->orderBy('priorty', 'desc')
            ->orderBy('created_at', 'desc')
            ->with('advantages')
            ->with('images')
            ->paginate(Constant::NUM_OF_PAGE);
        $currentUser = auth('sanctum')->user();
        $adsService = new AdsService();
        $res = $adsService->getAdsData($ads, $currentUser);
        return $this->get_response($res, 200, "completed");
    }

    public function getAllUser(Request $request)
    {
        $users = User::paginate(Constant::NUM_OF_PAGE);

        return $this->get_response($users->items(), 200, "completed");
    }
    public function acceptAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $ads = Ads::where("id", $request->ads_id)->first();
        $ads->status = Constant::ADS_ACCEPTED_STATE;
        $ads->save();
        try {
            $notificationService = new NotificationService();
            $ads = Ads::where('id', $request->ads_id)
                ->with('advantages')
                ->with('images')
                ->first();
            $user = User::where('id', '=', $ads->user_id)->get();
            $like = Like::where('ads_id', '=', $ads->id)->get();
            $comment = Comment::where('ads_id', '=', $ads->id)->orderBy('created_at', 'desc')->paginate(Constant::NUM_OF_PAGE);
            $comment_count = Comment::where('ads_id', '=', $ads->id)->count();


            if (count($user) == 0) {
                $ads->user = null;
            } else {
                $ads->user = $user[0];
            }
            $ads->like = count($like);
            $ads->comment = $comment->items();
            $ads->comment_count = $comment_count;
            $notificationService->sendNotification($ads, $ads->user_id);
        } catch (e) {
        }
        return $this->get_response($ads, 200, "completed");
    }

    public function blockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'block' => 'required|boolean',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $id = intval($request->user_id);
        $user = User::where("id", $id)->first();
        if ($user) {
            if ($user->blocked && $request->block) {
                return $this->get_error_response(401, "already blocked");
            } else if (!$user->blocked && !$request->block) {
                return $this->get_error_response(401, "user is not blocked");
            } else {
                $user->blocked = $request->block;
                $user->save();
            }
        } else {
            return $this->get_error_response(401, "user not found");
        }
        return $this->get_response([], 200, "block completed");
    }
    public function starAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
            'star' => 'required|boolean',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $id = intval($request->ads_id);
        $ads = Ads::where("id", $id)->first();
        if ($ads) {
            if ($ads->stared && $request->star) {
                return $this->get_error_response(401, "already stared");
            } else if (!$ads->stared && !$request->star) {
                return $this->get_error_response(401, "ads is not stared");
            } else {
                $ads->stared = $request->star;
                $ads->save();
            }
        } else {
            return $this->get_error_response(401, "ads not found");
        }
        return $this->get_response([], 200, "stared completed");
    }
    public function updatePriority(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
            'priority' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $id = intval($request->ads_id);
        $ads = Ads::where("id", $id)->first();
        if ($ads) {
            $ads->priorty = $request->priority;
            $ads->save();
        } else {
            return $this->get_error_response(401, "ads not found");
        }
        return $this->get_response([], 200, "update priority completed");
    }
    public function deleteAdsForAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $ads = Ads::where("id", $request->ads_id);
        if ($ads != null) {
            $ads->delete();
        } else {
            return $this->get_error_response(401, "no ads found");
        }
        return $this->get_response([], 200, "delete completed");
    }

    public function deleteCommentForAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $ads = Comment::where("id", $request->comment_id);
        if ($ads != null) {
            $ads->delete();
        } else {
            return $this->get_error_response(401, "no comment found");
        }
        return $this->get_response([], 200, "delete completed");
    }

    public function searchForUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_word' => 'required|string',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        
        $query = $request->search_word;
        $users = User::search($query);

        return $this->get_response($users, 200, "search complete");
    }
    public function sendFeedbackAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'title' => 'required|string',
            'description' => 'required|string',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $user = User::where("id", $request->user_id)->first();
        $userSetting = UserSetting::where("user_id", $request->user_id)->first();
        $user->fcm_token = $userSetting->fcm_token;
        if ($user != null) {
            $current_time = Carbon::now();
            FeedBack::create(['feed_back' => $request->description, 'sender_id' => $request->user()->id, 'receiver_id' => $request->user_id , "title" => $request->title]);
            $notificationService = new NotificationService();
            $notificationService->sendFeedbackNotificationToOneUser(["title" => $request->title, "description" => $request->description, "created_at" => $current_time], $user, $request->description);
        } else {
            return $this->get_error_response(401, "user not fount");
        }
        return $this->get_response([$user], 200, "send completed");
    }
}