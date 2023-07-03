<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\constant\Constant;
use App\Models\User;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use MyResponseTrait;

    public function getAllUser(Request $request)
    {
        $users = User::paginate(Constant::NUM_OF_PAGE);

        return $this->get_response($users->items(), 200, "completed");
    }
    public function acceptAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            $messages = $validator->messages();
            return $this->get_error_response(401, $messages);
        }
        $ads = Ads::where("id" , $request->ads_id)->first();
        $ads->status = Constant::ADS_ACCEPTED_STATE;
        $ads->save();
        return $this->get_response($ads, 200, "completed");
    } 
}
