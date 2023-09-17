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