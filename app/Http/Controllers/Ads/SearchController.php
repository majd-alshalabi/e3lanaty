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
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    use MyResponseTrait;

    public function searchForAds(Request $request)
    {  
        if ($request->search_word != null && $request->type != null) {
            $ads = Ads::where('name' ,'like' ,$request->search_word)->andWhere('type',$request->type)->orderBy('created_at', 'desc')->with('advantages')
                ->with('images')
                ->paginate(Constant::NUM_OF_PAGE)
            ;
            $currentUser = $request->user();
            foreach ($ads as $item) {
                $like = Like::where('ads_id', '=', $item->id)->get();
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
                $isInFavorite = Favorite::where([
                    ['ads_id', '=', $item->id],
                    ['user_id', '=', $currentUser->id]
                ])->count() > 0;
                $item->isInFavorite = $isInFavorite;
            }

            return $this->get_response($ads->items(), 200, "completed");
        }else if($request->type != null){
                $ads = Ads::where('type' , $request->type)->orderBy('created_at', 'desc')->with('advantages')
                ->with('images')
                ->paginate(Constant::NUM_OF_PAGE)
            ;
            $currentUser = $request->user(); 
            foreach ($ads as $item) {
                $like = Like::where('ads_id', '=', $item->id)->get();
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
                $isInFavorite = Favorite::where([
                    ['ads_id', '=', $item->id],
                    ['user_id', '=', $currentUser->id]
                ])->count() > 0;
                $item->isInFavorite = $isInFavorite;
            }

            return $this->get_response($ads->items(), 200, "completed");
        }else if($request->search_word != null){
                $ads = Ads::where('name','like',$request->search_word)->orderBy('created_at', 'desc')->with('advantages')
                ->with('images')
                ->paginate(Constant::NUM_OF_PAGE)
            ;
            $currentUser = $request->user(); 
            foreach ($ads as $item) {
                $like = Like::where('ads_id', '=', $item->id)->get();
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
                $isInFavorite = Favorite::where([
                    ['ads_id', '=', $item->id],
                    ['user_id', '=', $currentUser->id]
                ])->count() > 0;
                $item->isInFavorite = $isInFavorite;
            }

            return $this->get_response($ads->items(), 200, "completed");
        }
        return $this->get_error_response(401, "enter data to search for");

    }
}