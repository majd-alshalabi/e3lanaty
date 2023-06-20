<?php

use App\Http\Controllers\Ads\AdsController;
use App\Http\Controllers\Ads\CommentController;
use App\Http\Controllers\Ads\FavoriteController;
use App\Http\Controllers\Ads\LikeController;
use App\Http\Controllers\auth_controller\FeedBackController;
use App\Http\Controllers\auth_controller\FollowController;
use App\Http\Controllers\auth_controller\LoginController;
use App\Http\Controllers\auth_controller\RegisterController;
use App\Http\Controllers\auth_controller\UpdateFcmTokenController;
use App\Http\Controllers\auth_controller\UserSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [LoginController::class, 'login']);
Route::post('/login_admin', [LoginController::class, 'loginAdmin']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/register_admin', [RegisterController::class, 'registerAdmin']);

Route::get('/storage/public/images/{filename}', [AdsController::class, 'getImage']);



Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/logout_admin', [LoginController::class, 'logoutAdmin']);
    Route::post('/add_ads', [AdsController::class, 'addAds']);
    Route::post('/upload_image', [AdsController::class, 'uploadImage']);
    Route::get('/get_all_ads', [AdsController::class, 'getAllAds']);
    Route::get('/get_all_favorite', [FavoriteController::class, 'getAllFavorite']);
    Route::post('/get_ads_by_id', [AdsController::class, 'get_ads_by_id']);
    Route::get('/get_all_feedback', [FeedBackController::class, 'getAllFeedback']);
    Route::post('/like', [LikeController::class, 'addLike']);
    Route::post('/favorite', [FavoriteController::class, 'addFavorite']);
    Route::post('/comment', [CommentController::class, 'addComment']);
    Route::post('/follow', [FollowController::class, 'addFollow']);
    Route::post('/add_feed_back', [FeedBackController::class, 'addFeedback']);
    Route::post('/delete_feed_back', [FeedBackController::class, 'deleteFeedback']);
    Route::post('/update_notification_setting', [UserSettingController::class, 'updateNotificationType']);
    Route::post('/update_fcm_token', [UpdateFcmTokenController::class, 'updateFcmToken']);
});
