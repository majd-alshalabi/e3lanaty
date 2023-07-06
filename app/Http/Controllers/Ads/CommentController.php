<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\Models\constant\Constant;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;
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
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }

        $user = $request->user();
        $ads = Ads::where("id", "=", $request->ads_id);
        $adsUser = User::where("id" , $ads->user_id);
        NotificationService::
        if ($ads == null) {
            return $this->get_error_response(401, "this ad isn't avalible");
        }

        $commentRes = Comment::create(['comment' => $request->comment, 'user_id' => $user->id, "ads_id" => $request->ads_id]);
        $commentRes->user = $user ;
        return $this->get_response($commentRes, 200, "add like completed");
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
        $comment = Comment::where('ads_id' , $request->ads_id)->orderBy('created_at', 'desc')
            ->paginate(Constant::NUM_OF_PAGE)
        ;
        foreach ($comment as $item) {
            $user = User::where('id', '=', $item->user_id)->get();
            $item->user = $user[0];
        }

        return $this->get_response($comment->items(), 200, "completed");
    }
}