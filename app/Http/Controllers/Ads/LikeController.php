<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Like;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    use MyResponseTrait;

    public function addLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'like' => 'required|boolean',
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        $ads = Ads::where("id" , "=" , $request->ads_id);
        if($ads == null){
            return $this->get_error_response(401, "this ad isn't avalible");
        }
        if($request->like){
            $exists = Like::where('ads_id' , $request->ads_id)
                -> where('user_id' , $user->id)
                ->exists();
            if($exists){
                return $this->get_error_response(401, "you have already likes this ad");
            } 
            else {
                Like::create(['like' => true, 'user_id' => $user->id , "ads_id" => $request->ads_id]);
            }
        }else {
            $exists = Like::where('ads_id' , $request->ads_id)
                -> where('user_id' , $user->id)
                ->exists();
            if(!$exists){
                return $this->get_error_response(401, "you have not liked this ad yet");
            } 
            else {
                Like::where('user_id', $user->id)
                    ->where('ads_id', $request->ads_id)
                    ->delete();
            }
        }
        return $this->get_response([], 200, "add like completed");
    }
}
