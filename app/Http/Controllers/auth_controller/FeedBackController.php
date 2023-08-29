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
        $feedback = null;
        if ($user->admin) {
            $receiver = User::where("id" , $request->receiver_id)->first();
            $feedback = FeedBack::create(['feed_back' => $request->feed_back, 'sender_id' => $user->id, 'receiver_id' => $receiver->id , "title" => $request->title]);
        } else {
            $admin = User::where("admin" , true)->first();
            $feedback = FeedBack::create(['feed_back' => $request->feed_back, 'sender_id' => $user->id, 'receiver_id' => $admin->id , "title" => $request->title]);
        }
        return $this->get_response($feedback, 200, "add feedback completed");
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
        if ($res == 0) {
            return $this->get_error_response(401, "this feedback is no longer exist");
        }
        return $this->get_response([], 200, "delete feedback completed");
    }

    public function getAllUserSentFeedBack(Request $request)
    {
        $user = $request->user();
        $admin_id = $user->id;

        $feedbacks = FeedBack::where('receiver_id', $admin_id)->get();
        $userIds = $feedbacks->pluck('sender_id')->unique()->toArray();

        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $currentUser) {
            $currentUser->not_readed_feedback_count = $feedbacks->where("sender_id" , $currentUser->id)->where("read_status" , false)->count();
        }

        return $this->get_response($users, 200, "Get all users with unread feedback count");
    }

    public function getFeedbackById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $feedbacks = FeedBack::where("sender_id" , $request->id)->orWhere("receiver_id" , $request->id)->with("sender")->with("receiver")->get();
        return $this->get_response($feedbacks, 200, "get all favorite completed");
    }

    public function updateFeedbackToReaded(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $feedbacks = FeedBack::where("id" , $request->id)->first();
        $feedbacks->read_status = true ;
        $feedbacks->save();
        return $this->get_response($feedbacks, 200, "get all favorite completed");
    }
}