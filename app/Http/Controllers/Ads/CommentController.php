<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\Posts;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use MyResponseTrait;
    public function addComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'ads_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        if($user->blocked){
            return $this->get_error_response(401, "user is blocked");
        }
        
        $ads = Ads::find($request->ads_id);

        if (!$ads) {
            return $this->get_error_response(401, "This ad isn't available");
        }

        $comment = Comment::create([
            'comment' => $request->comment,
            'ads_id' => intval($request->ads_id),
            'user_id' => $user->id,
        ]);

        $comment->user = $user;
        $adsUser = User::where("id", $ads->user_id)->first();

        if ($adsUser->id !== $user->id) {
            $notificationService = new NotificationService();
            $notificationService->sendCommentNotificationToOneUser($comment, $adsUser);
        }

        return $this->get_response($comment, 200, "Comment added successfully");
    }
    public function getAllComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $comment = Comment::where('ads_id', $request->ads_id)->orderBy('created_at', 'desc')
            ->paginate(Constant::NUM_OF_PAGE)
        ;
        foreach ($comment as $item) {
            $user = User::where('id', '=', $item->user_id)->get();
            $item->user = $user[0];
        }

        return $this->get_response($comment->items(), 200, "completed");
    }
}