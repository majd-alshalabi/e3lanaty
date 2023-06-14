<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Comment;
use App\response_trait\MyResponseTrait;
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
        if ($ads == null) {
            return $this->get_error_response(401, "this ad isn't avalible");
        }

        Comment::create(['comment' => $request->comment, 'user_id' => $user->id, "ads_id" => $request->ads_id]);

        return $this->get_response([], 200, "add like completed");
    }
}