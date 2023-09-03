<?php

namespace App\Services;

use App\Models\Ads;
use App\Models\AdsDescription;
use App\Models\Advantage;
use App\Models\constant\Constant;
use App\Models\Image;
use App\Models\Posts;

class AddAdsService
{
    public function addAds($request , bool $sendNotification)
    {
        $user = $request->user();

        $isAdmin = false;
        if ($user->admin) {
            $isAdmin = true;
        }
        $ads = Ads::create([
            'name' => $request->name,
            'type' => $request->type,
            'price' => $request->price,
            'link' => $request->link,
            'status' => $isAdmin ? Constant::ADS_ACCEPTED_STATE : Constant::ADS_PENDDING_STATE,
            'priority' => 0,
            'user_id' => $user->id,
            'admin' => $isAdmin,
            'service' => $request->ads_type == Constant::SERVICE_ADS_TYPE,
            'ads_type' => $request->ads_type,
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
        $description = array();
        if ($request->description != null) {
            foreach ($request->description as $item) {
                $description[] = AdsDescription::create([
                    'image' => $item['image'],
                    'ads_id' => $ads->id,
                    'html_code' => $item['html_code'],
                    'description' => $item['description'],
                ]);
            }
        }
        $images = array();
        if ($request->images != null) {
            foreach ($request->images as $item) {
                $images[] = Image::create([
                    'path' => $item,
                    'ads_id' => $ads->id,
                    'is_extra' => false,
                ]);
            }
        }
        $ads->user = $user;
        $ads->description = $description;
        $ads->advantages = $advantages;
        $ads->images = $images;
        $ads->like = 0;
        $ads->comment_count = 0;
        $ads->comment = null;
        $ads->isInFavorite = false;
        if ($sendNotification) {
            $notificationService = new NotificationService();
            $notificationService->sendNotification($ads, $ads->user_id);
        }

        return $ads;
        
    }

    public function addPost($request , bool $sendNotification)
    {
        $user = $request->user();

        $isAdmin = false;
        if ($user->admin) {
            $isAdmin = true;
        }
        $post = Ads::create([
            'name' => $request->name,
            'status' => $isAdmin ? Constant::ADS_ACCEPTED_STATE : Constant::ADS_PENDDING_STATE,
            'priority' => 0,
            'user_id' => $user->id,
            'admin' => $isAdmin,
            'ads_type' => $request->ads_type,
        ]);
        $images = array();
        if ($request->images != null) {
            foreach ($request->images as $item) {
                $images[] = Image::create([
                    'path' => $item,
                    'ads_id' => $post->id,
                    'is_extra' => false,
                ]);
            }
        }
        $description = array();
        if ($request->description != null) {
            foreach ($request->description as $item) {
                $description[] = AdsDescription::create([
                    'image' => $item['image'],
                    'ads_id' => $post->id,
                    'html_code' => $item['html_code'],
                    'description' => $item['description'],
                ]);
            }
        }
        $post->description = $description;
        $post->user = $user;
        $post->images = $images;
        $post->like = 0;
        $post->comment_count = 0;
        $post->isInFavorite = false;
        if ($sendNotification) {
            $notificationService = new NotificationService();
            $notificationService->sendNotification($post, $post->user_id);
        }

        return $post;
    }
}