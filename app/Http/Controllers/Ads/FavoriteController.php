<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\Like;
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
        $favorits = Favorite::where('user_id' , '=' , $user->id)->get();
        foreach ($favorits as $favorit) {
            $ads = Ads::with('advantages')
                ->with('images')->where('id', $favorit->ads_id)->first()
            ;
            $like = Like::where('ads_id', '=', $ads->id)->get();
            $comment = Comment::where('ads_id', '=', $ads->id)->paginate(Constant::NUM_OF_PAGE);
            $comment_count = Comment::where('ads_id', '=', $ads->id)->count();

            $ads->user = $user;

            $ads->like = count($like);
            $ads->comment = $comment->items();
            $ads->comment_count = $comment_count;
            $ads->isInFavorite = true ;
            $favorit->ads = $ads;
        }
        return $this->get_response($favorits, 200, "get all favorite completed");
    }
}