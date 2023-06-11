<?php
namespace App\response_trait;


Trait MyResponseTrait
{
    public function get_response($data,$status,$msg)
    {
        $arr = [
            'data'=>$data,
            'status'=>$status,
            'message'=>$msg
        ];
        return response($arr);
    }
    
    public function get_response_with_only_message_and_status($status,$msg)
    {
        $arr = [
            'status'=>$status,
            'message'=>$msg
        ];
        return response($arr);
    }

    public function get_response_for_login($data,$status,$msg,$token)
    {
        $arr = [
            'data'=>$data,
            'token'=>$token,
            'status'=>$status,
            'message'=>$msg
        ];
        return response($arr);
    }

    public function get_error_response($status,$msg)
    {
        $arr = [
            'status'=>$status,
            'message'=>$msg
        ];
        return response($arr);
    }
}