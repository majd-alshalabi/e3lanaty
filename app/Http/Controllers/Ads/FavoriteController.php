<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Like;
use App\Models\Posts;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    use MyResponseTrait;

    public function addFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favorite' => 'required|boolean',
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        $ads = Ads::where("id", "=", $request->ads_id)->first();
        if ($ads == null) {
            return $this->get_error_response(401, "this ad isn't avalible");
        }
        if ($request->favorite) {
            $exists = Favorite::where('ads_id', $request->ads_id)
                ->where('user_id', $user->id)
                ->exists();
            if ($exists) {
                return $this->get_error_response(401, "you have already favorites this ad");
            } else {
                Favorite::create(['favorite' => true, 'user_id' => $user->id, "ads_id" => $request->ads_id]);
            }
        } else {
            $exists = Favorite::where('ads_id', $request->ads_id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$exists) {
                return $this->get_error_response(401, "you have not add this ad to your favorites yet");
            } else {
                Favorite::where('user_id', $user->id)
                    ->where('ads_id', $request->ads_id)
                    ->delete();
            }
        }
        return $this->get_response($request->favorite, 200, "add favorite completed");
    }
    public function getAllFavorite(Request $request)
{
    $user = $request->user();

    // Retrieve favorites with related ads information in a single query
    $favorites = Favorite::where('user_id', $user->id)
        ->with(['ads' => function ($query) {
            $query->with('advantages')
                ->with('images');
        }])
        ->get();

    foreach ($favorites as $favorite) {
        $ads = $favorite->ads;

        // Fetch the number of likes for this ad
        $likeCount = $ads->likes()->count();

        // Check if the current user has liked this ad
        $isLike = $ads->likes()->where('user_id', $user->id)->exists();

        // Fetch comments for the ad with pagination
        $comments = $ads->comments()
            ->paginate(Constant::NUM_OF_PAGE);

        $ads->user = User::where("id" , $ads->user_id)->first();
        $ads->isLike = $isLike;
        $ads->like = $likeCount;
        $ads->comment = $comments->items();
        $ads->comment_count = $comments->total();
        $ads->isInFavorite = true;
    }

    return $this->get_response($favorites, 200, "get all favorite completed");
}

}