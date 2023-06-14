<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\constant\Constant;
use App\Models\FeedBack;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedBackController extends Controller
{
    use MyResponseTrait;
    public function addFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_back' => 'required|string',
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();

        FeedBack::create(['feed_back' => $request->feed_back, 'user_id' => $user->id, "title" => $request->title]);

        return $this->get_response([], 200, "add feedback completed");
    }
    public function deleteFeedBack(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_back_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $res = FeedBack::where('id', $request->feed_back_id)
            ->delete();
        if($res == 0){
            return $this->get_error_response(401, "this feedback is no longer exist");
        }
        return $this->get_response([], 200, "delete feedback completed");
    }

    public function getAllFeedback(Request $request)
    {
        $feedbacks = FeedBack::with('users')->paginate(Constant::NUM_OF_PAGE);
        return $this->get_response($feedbacks->items(), 200, "get all favorite completed");
    }
}
