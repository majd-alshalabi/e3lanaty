<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\AdsDescription;
use App\Models\Advantage;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Image;
use App\Models\Like;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use App\Services\AddAdsService;
use App\Services\AdsService;
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
            'ads_type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        // return [$request->ads_type];
        if ($request->ads_type == Constant::POST_ADS_TYPE) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return $this->get_error_response(401, $messages);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'type' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return $this->get_error_response(401, $messages);
            }
        }
        $sendNotification = false;
        if ($request->send_notification == true) {
            $sendNotification = true;
        }

        $service = new AddAdsService();
        if ($request->user()->blocked) {
            return $this->get_error_response(401, "user is blocked");
        }
        $res = null;
        $res = ($request->ads_type == Constant::POST_ADS_TYPE)
            ? $service->addPost($request, $sendNotification)
            : $service->addAds($request, $sendNotification)
        ;

        return $this->get_response([$res], 200, "add completed");
    }
    public function updateAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->get_error_response(401, $validator->messages());
        }

        $ads = Ads::find($request->id);

        if ($ads == null) {
            return $this->get_error_response(401, "Can't find ads, please try again later");
        }

        $ads->name = $request->input('name');
        $ads->price = $request->input('price');
        $ads->link = $request->input('link');
        $ads->type = $request->input('type');

        $ads->save();

        Advantage::where("ads_id", $request->id)->delete();
        $advantages = array();
        if ($request->has('advantages') && is_array($request->input('advantages'))) {
            foreach ($request->input('advantages') as $item) {
                $advantages[] = Advantage::create([
                    'advantage' => $item,
                    'ads_id' => $ads->id,
                ]);
            }
        }
        $ads->advantages = $advantages;

        AdsDescription::where("ads_id", $request->id)->delete();
        $description = array();
        if ($request->has('description') && is_array($request->input('description'))) {
            foreach ($request->input('description') as $item) {
                $description[] = AdsDescription::create([
                    'image' => $item['image'],
                    'ads_id' => $ads->id,
                    'html_code' => $item['html_code'],
                    'description' => $item['description'],
                ]);
            }
        }
        $ads->description = $description;

        Image::where("ads_id", $request->id)->delete();
        $images = array();
        if ($request->has('images') && is_array($request->input('images'))) {
            foreach ($request->input('images') as $item) {
                $images[] = Image::create([
                    'path' => $item,
                    'ads_id' => $ads->id,
                    'is_extra' => false,
                ]);
            }
        }
        $ads->images = $images;
        $ads->user = User::where('id' , $ads->user_id)->first();
        $ads->updated = true ;
        if ($request->send_notification) {
            $notificationService = new NotificationService();
            $notificationService->sendNotification($ads, $ads->user_id,true);
        }
        return $this->get_response([$ads], 200, "Update completed");
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
        $ads = Ads::where([["id", $request->ads_id], ["user_id", $user->id]]);
        if ($ads != null) {
            $ads->delete();
        } else {
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
        $currentUser = auth('sanctum')->user();
        $adsService = new AdsService();
        $res = $adsService->getAdsData($ads, $currentUser);

        return $this->get_response($res, 200, "completed");
    }

    public function getUserPendingAds(Request $request)
    {

        $currentUser = auth('sanctum')->user();

        $ads = Ads::where([
            ['status', '=', Constant::ADS_PENDDING_STATE],
            ['user_id', '=', $currentUser->id]
        ])
            ->orderBy('created_at', 'desc')
            ->with('advantages')
            ->with('images')
            ->get();
        $adsService = new AdsService();
        $res = $adsService->getAdsData($ads, $currentUser);

        return $this->get_response($res, 200, "completed");
    }

    public function getAllAdsWithPenddingState(Request $request)
    {
        $ads = Ads::where('status', Constant::ADS_PENDDING_STATE)->orderBy('created_at', 'desc')->with('advantages')
            ->with('images')
            ->paginate(Constant::NUM_OF_PAGE)
        ;
        $currentUser = auth('sanctum')->user();
        $adsService = new AdsService();
        $res = $adsService->getAdsData($ads, $currentUser);

        return $this->get_response($res, 200, "completed");
    }

    public function getAllAdsWithAcceptedState(Request $request)
    {
        $ads = null;
        if ($request->ads_type != Constant::SERVICE_ADS_TYPE || $request->ads_type == Constant::POST_ADS_TYPE) {
            $ads = Ads::where('ads_type', "=", $request->ads_type)
                ->orderBy('priorty', 'desc')
                ->where('status', Constant::ADS_ACCEPTED_STATE)
                ->orderBy('created_at', 'desc')->with('advantages')
                ->with('images')
                ->paginate(Constant::NUM_OF_PAGE)
            ;
        } else {
            $ads = Ads::where('ads_type', "=", $request->ads_type)
                ->orderBy('priorty', 'desc')
                ->where('status', Constant::ADS_ACCEPTED_STATE)
                ->orderBy('created_at', 'desc')->with('advantages')
                ->with('images')
                ->paginate(Constant::NUM_OF_PAGE)
            ;
        }
        $currentUser = auth('sanctum')->user();
        $adsService = new AdsService();
        $res = $adsService->getAdsData($ads, $currentUser);

        return $this->get_response($res, 200, "completed");
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

        $ads = Ads::where('user_id', $request->user_id)->where('status', Constant::ADS_ACCEPTED_STATE)->orderBy('created_at', 'desc')->with('advantages')
            ->with('images')->get()
        ;

        $currentUser = auth('sanctum')->user();
        $adsService = new AdsService();
        $res = $adsService->getAdsData($ads, $currentUser);
        $isFollowing = false;
        if ($currentUser != null)
            $isFollowing = Follow::where([
                ['follower_id', '=', $currentUser->id],
                ['followed_id', '=', $request->user_id]
            ])->count() > 0;
        return $this->get_response(["ads" => $res, "isFollowing" => $isFollowing], 200, "completed");
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
        $currentUser = auth('sanctum')->user();

        $ads = Ads::where('id', $request->ads_id)
            ->with('advantages')
            ->with('images')
            ->first();
        if ($ads == null)
            return $this->get_error_response(401, "ads is not available");

        $user = User::where('id', '=', $ads->user_id)->get();
        $like = Like::where('ads_id', '=', $ads->id)->get();
        $description = AdsDescription::where('ads_id', '=', $ads->id)->get();
        $isLike = false;
        if ($currentUser != null)
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
        $ads->description = $description;
        $ads->comment = $comment->items();
        $ads->comment_count = $comment_count;
        $isInFavorite = false;
        if ($currentUser != null) {
            $isInFavorite = Favorite::where([
                ['ads_id', '=', $request->ads_id],
                ['user_id', '=', $currentUser->id]
            ])->count() > 0;
        }
        $ads->isInFavorite = $isInFavorite;

        return $this->get_response($ads, 200, "completed");
    }


}