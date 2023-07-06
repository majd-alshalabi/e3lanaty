<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Like;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use MyResponseTrait;

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
        $ads = Ads::where("id" , $request->ads_id)->first();
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
    public function deleteAdsForAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $ads = Ads::where("id" , $request->ads_id);
        if($ads != null){
            $ads->delete();
        }
        else {
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
        $ads = Comment::where("id" , $request->comment_id);
        if($ads != null){
            $ads->delete();
        }
        else {
            return $this->get_error_response(401, "no comment found");
        }
        return $this->get_response([], 200, "delete completed");
    }
}
