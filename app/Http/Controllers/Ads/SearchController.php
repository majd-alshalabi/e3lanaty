<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use MyResponseTrait;

    public function searchForAds(Request $request)
    {
        $searchField = [['status', Constant::ADS_ACCEPTED_STATE]];
        if ($request->has('search_word') && !empty($request->search_word)) {
            $searchField[] = ['name', 'LIKE', '%' . $request->search_word . '%'];
        }

        if ($request->has('type') && isset($request->type)) {
            $searchField[] = ['type', $request->type];
        }

        $ads = Ads::where($searchField)->orderBy('created_at', 'desc')->with('advantages')
            ->with('images')
            ->paginate(Constant::NUM_OF_PAGE)
        ;
        $currentUser = auth('sanctum')->user();
        foreach ($ads as $item) {
            $like = Like::where('ads_id', '=', $item->id)->get();
            $isLike = false; 
            if($currentUser != null)
            $isLike = Like::where([
                ['ads_id', '=', $item->id],
                ['user_id', '=', $currentUser->id]
            ])->count() > 0;
            $comment = Comment::where('ads_id', '=', $item->id)->orderBy('created_at', 'desc')->paginate(Constant::NUM_OF_PAGE);
            $comment_count = Comment::where('ads_id', '=', $item->id)->count();
            $user = User::where('id', '=', $item->user_id)->get();

            if (count($user) == 0) {
                $item->user = null;
            } else {
                $item->user = $user[0];
            }
            $item->like = count($like);
            $commentRes = [];
            foreach ($comment->items() as $item2) {
                $commentUser = User::where('id', '=', $item2->user_id)->get();
                $item2->user = $commentUser[0];
                $commentRes[] = $item2;
            }
            $item->comment = $commentRes;
            $item->isLike = $isLike;
            $item->comment_count = $comment_count;
            $isInFavorite = false; 
            if($currentUser != null)
            $isInFavorite = Favorite::where([
                ['ads_id', '=', $item->id],
                ['user_id', '=', $currentUser->id]
            ])->count() > 0;
            $item->isInFavorite = $isInFavorite;
        }

        return $this->get_response($ads->items(), 200, "completed");

    }
}