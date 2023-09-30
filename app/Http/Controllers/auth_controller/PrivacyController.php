<?php

namespace App\Http\Controllers\auth_controller;

use App\Http\Controllers\Controller;
use App\Models\Privacy;
use App\response_trait\MyResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PrivacyController extends Controller
{
    use MyResponseTrait;
    public function getPrivacy()
    {
        $privacy = Privacy::all()->first();
        if($privacy == null){
            return $this->get_error_response(401, "there is no privacy currently");
        }
        return $this->get_response($privacy, 200, "get privacy done");
    }

    public function updatePrivacy(Request $request)
    {
        $privacy = Privacy::all()->first();
        if($privacy == null){
            $privacy = Privacy::create([
                'privacy' => $request->privacy,
                'terms' => $request->terms,
                'cookies' => $request->current_cookies,
            ]);
        }
        else {
            if($request->privacy != null){
                $privacy->privacy = $request->privacy;
            }
            if($request->terms != null){
                $privacy->terms = $request->terms;
            }
            if($request->current_cookies != null){
                $privacy->cookies = $request->current_cookies;
            }
            $privacy->save();
        }
        return $this->get_response($privacy, 200, "update privacy completed");
    }
}
