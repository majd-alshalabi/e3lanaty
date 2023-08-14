<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    use MyResponseTrait;

    public function addFollow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'follow' => 'required|boolean'
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        $followed_exists = User::where('id', $request->user_id)->exists();
        if (!$followed_exists) {
            return $this->get_error_response(401, "the user you are following is no longer existing");
        }
        if ($user->id == $request->user_id) {
            return $this->get_error_response(401, "you can't follow yourself");
        }
        if ($request->follow) {
            $exists = Follow::where('follower_id', $user->id)
                ->where('followed_id', $request->user_id)
                ->exists();
            if ($exists) {
                return $this->get_error_response(401, "you have already followed this user");
            } else {
                Follow::create(['follower_id' => $user->id, "followed_id" => $request->user_id]);
            }
        } else {
            $exists = Follow::where('follower_id', $user->id)
                ->where('followed_id', $request->user_id)
                ->exists();
            if (!$exists) {
                return $this->get_error_response(401, "you have not followed this user yet");
            } else {
                Follow::where('follower_id', $user->id)
                    ->where('followed_id', $request->user_id)
                    ->delete();
            }
        }
        return $this->get_response($request->follow, 200, "add follow completed");
    }
    public function getFollowingAndFollowerAndFavorite(Request $request)
    {
        $user = $request->user();

        $favoritCount = Ads::where('user_id', $user->id)->count();
        $folowerCount = Follow::where('followed_id', $user->id)->count();
        $folowingCount = Follow::where('follower_id', $user->id)->count();

        return $this->get_response(["user" => $user,"favorite_count" => $favoritCount, "follower_count" => $folowerCount, "following_count" => $folowingCount], 200, "completed");
    }

    public function getFollowerOrFollowing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'follower' => 'required|boolean'
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        $res = null ;
        if($request->follower)
        $res = $folower = Follow::where('followed_id', $user->id)->with("follower")->get();
        else 
        $res = $folowing = Follow::where('follower_id', $user->id)->with("following")->get();

        return $this->get_response($res, 200, "completed");
    }

}