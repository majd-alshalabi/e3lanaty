<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Advantage;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Image;
use App\Models\Like;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;
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
        $ads = Ads::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'price' => $request->price,
            'link' => $request->link,
            'extra_description' => $request->extra_description,
            'status' => 0,
            'priority' => 0,
            'user_id' => $user->id,
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
                ]);
            }
        }
        $ads->user = $user;
        $ads->advantages = $advantages;
        $ads->images = $images;
        $ads->like = 0;
        $ads->comment_count = 0;
        $ads->comment = null;
        try {
            $notificationService = new NotificationService();
            $notificationService->sendNotification($ads,$user->id);
        } catch (e) {

        }
        return $this->get_response([$ads], 200, "add completed");
    }

        public function getImage($filename)
    {
        $path = public_path('images').'/' . $filename;

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
        }else {
            return $this->get_error_response(401, "enter file to upload");
        }

        return $this->get_response($imageName, 200, "add completed");
    }
   
    public function getAllAds(Request $request)
    {
        $ads = Ads::with('advantages')
            ->with('images')
            ->paginate(Constant::NUM_OF_PAGE)
        ;
        foreach ($ads as $item) {
            $like = Like::where('ads_id', '=', $item->id)->get();
            $comment = Comment::where('ads_id', '=', $item->id)->paginate(Constant::NUM_OF_PAGE);
            $comment_count = Comment::where('ads_id', '=', $item->id)->count();
            $user = User::where('id', '=', $item->user_id)->get();

            if (count($user) == 0) {
                $item->user = null;
            } else {
                $item->user = $user[0];
            }
            $item->like = count($like);
            $item->comment = $comment->items();
            $item->comment_count = $comment_count;
        }

        return $this->get_response($ads->items(), 200, "completed");
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
        $ads = Ads::where('id', $request->ads_id)
            ->with('advantages')
            ->with('images')
            ->first();
        $like = Like::where('ads_id', '=', $ads->id)->get();
        $comment = Comment::where('ads_id', '=', $ads->id)->paginate(Constant::NUM_OF_PAGE);
        $comment_count = Comment::where('ads_id', '=', $ads->id)->count();
        $user = User::where('id', '=', $ads->user_id)->get();

        if (count($user) == 0) {
            $ads->user = null;
        } else {
            $ads->user = $user[0];
        }
        $ads->like = count($like);
        $ads->comment = $comment->items();
        $ads->comment_count = $comment_count;


        return $this->get_response($ads, 200, "completed");
    }




}