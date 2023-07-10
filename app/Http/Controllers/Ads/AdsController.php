<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Advantage;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Image;
use App\Models\Like;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class AdsController extends Controller
{
    use MyResponseTrait;
    public function addAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'type' => 'required|integer',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'name' => 'required|string',
            'link' => 'required|string',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);   
        }
        
        $user = $request->user();
    
        $isAdmin = false ;
        if($user instanceof \App\Models\Admin){
            $isAdmin = true ;
        }
        $ads = Ads::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'price' => $request->price,
            'link' => $request->link,
            'extra_description' => $request->extra_description,
            'status' => Constant::ADS_PENDDING_STATE,
            'priority' => 0,
            'user_id' => $user->id,
            'admin' => $isAdmin,
        ]);
        $advantages = array();
        if ($request->advantages != null) {
            foreach ($request->advantages as $item) {
                $advantages[] = Advantage::create([
                    'advantage' => $item,
                    'ads_id' => $ads->id,
                ]);
            }
        }
        $images = array();
        if ($request->images != null) {
            foreach ($request->images as $item) {
                $images[] = Image::create([
                    'path' => $item,
                    'ads_id' => $ads->id,
                    'is_extra' => false,
                ]);
            }
        }
        if ($request->extra_images != null) {
            foreach ($request->extra_images as $item) {
                $images[] = Image::create([
                    'path' => $item,
                    'ads_id' => $ads->id,
                    'is_extra' => true,
                ]);
            }
        }
        $ads->user = $user;
        $ads->advantages = $advantages;
        $ads->images = $images;
        $ads->like = 0;
        $ads->comment_count = 0;
        $ads->comment = null;
        $ads->isInFavorite = false;

        return $this->get_response([$ads], 200, "add completed");
    }

    public function deleteAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $user = $request->user();
        $ads = Ads::where([["id" , $request->ads_id],["user_id",$user->id]]);
        if($ads != null){
            $ads->delete();
        }
        else {
            return $this->get_error_response(401, "this ads is not your's you cant delete it");
        }
        return $this->get_response([], 200, "delete completed");
    }

    public function getImage($filename)
    {
        $path = public_path('images') . '/' . $filename;

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);

        return $response;
    }
    public function uploadImage(Request $request)
    {

        $imageName = '';

        if ($request->image != null) {
            $imageName = "public/images/" . time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
        } else {
            return $this->get_error_response(401, "enter file to upload");
        }

        return $this->get_response($imageName, 200, "add completed");
    }

    public function getAllAds(Request $request)
    {
        $ads = Ads::orderBy('created_at', 'desc')->with('advantages')
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

    public function getUserPendingAds(Request $request)
    {
        
        $currentUser = $request->user(); 

        $ads = Ads::where([
            ['status', '=', Constant::ADS_PENDDING_STATE],
            ['user_id', '=', $currentUser->id]
        ])
        ->orderBy('created_at', 'desc')
        ->with('advantages')
        ->with('images')
        ->get();
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

        return $this->get_response($ads, 200, "completed");
    }

    public function getAllAdsWithPenddingState(Request $request)
    {
        $ads = Ads::where('status' , Constant::ADS_PENDDING_STATE)->orderBy('created_at', 'desc')->with('advantages')
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

    public function getAllAdsWithAcceptedState(Request $request)
    {
        $ads = Ads::where('admin' , false)->orderBy('priorty','desc')->where('status' , Constant::ADS_ACCEPTED_STATE) ->orderBy('created_at', 'desc')->with('advantages')
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


    public function getAdsByUserId(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $ads = Ads::where('user_id' , $request->user_id)->where('status' , Constant::ADS_ACCEPTED_STATE)->orderBy('created_at', 'desc')->with('advantages')
            ->with('images')->get()
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
        $isFollowing = Follow::where([
            ['follower_id', '=', $request->user()->id],
            ['followed_id', '=', $request->user_id]
        ])->count() > 0;
        return $this->get_response(["ads" => $ads , "isFollowing" => $isFollowing], 200, "completed");
    }

    public function get_ads_by_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $currentUser = $request->user(); 

        $ads = Ads::where('id', $request->ads_id)
            ->with('advantages')
            ->with('images')
            ->first();
        $user = User::where('id', '=', $ads->user_id)->get();
        $like = Like::where('ads_id', '=', $ads->id)->get();
        $isLike = Like::where([
            ['ads_id', '=', $ads->id],
            ['user_id', '=', $currentUser->id]
        ])->count() > 0;
        $comment = Comment::where('ads_id', '=', $ads->id)->orderBy('created_at', 'desc')->paginate(Constant::NUM_OF_PAGE);
        $comment_count = Comment::where('ads_id', '=', $ads->id)->count();


        if (count($user) == 0) {
            $ads->user = null;
        } else {
            $ads->user = $user[0];
        }
        $ads->like = count($like);
        $ads->isLike = $isLike;
        $ads->comment = $comment->items();
        $ads->comment_count = $comment_count;
        $isInFavorite = Favorite::where([
            ['ads_id', '=', $request->ads_id],
            ['user_id', '=', $currentUser->id]
        ])->count() > 0;
        $ads->isInFavorite = $isInFavorite;

        return $this->get_response($ads, 200, "completed");
    }


}