<?php

use App\Http\Controllers\Ads\AdsController;
use App\Http\Controllers\Ads\CommentController;
use App\Http\Controllers\Ads\FavoriteController;
use App\Http\Controllers\Ads\LikeController;
use App\Http\Controllers\Ads\SearchController;
use App\Http\Controllers\auth_controller\AdminController;
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
Route::post('/check_email', [RegisterController::class, 'checkEmail']);
Route::get('/storage/public/images/{filename}', [AdsController::class, 'getImage']);
Route::get('/get_all_ads_with_accepted_status', [AdsController::class, 'getAllAdsWithAcceptedState']);
Route::post('/get_all_comment', [CommentController::class, 'getAllComment']);
Route::post('/get_ads_by_id', [AdsController::class, 'get_ads_by_id']);
Route::post('/search_for_ads', [SearchController::class, 'searchForAds']);
Route::get('/get_admin_or_star_ads', [AdminController::class, 'getAdminAndStaredAds']);



Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/delete_account', [LoginController::class, 'deleteAccount']);
    Route::post('/logout_admin', [LoginController::class, 'logoutAdmin']);
    Route::post('/add_ads', [AdsController::class, 'addAds']);
    Route::post('/upload_image', [AdsController::class, 'uploadImage']);
    Route::post('/update_profile_image', [UserSettingController::class, 'uploadImage']);
    Route::post('/update_name', [UserSettingController::class, 'updateName']);
    Route::get('/get_all_user', [AdminController::class, 'getAllUser']);
    Route::post('/delete_ads', [AdsController::class, 'deleteAds']);
    Route::get('/get_all_ads_with_pending_status', [AdsController::class, 'getAllAdsWithPenddingState']);
    Route::get('/get_user_pending_ads', [AdsController::class, 'getUserPendingAds']);
    Route::get('/get_all_ads', [AdsController::class, 'getAllAds']);
    Route::post('/get_ads_by_user_id', [AdsController::class, 'getAdsByUserId']);
    Route::get('/get_all_favorite', [FavoriteController::class, 'getAllFavorite']);
    Route::get('/get_all_feedback', [FeedBackController::class, 'getAllFeedback']);
    Route::post('/like', [LikeController::class, 'addLike']);
    Route::post('/favorite', [FavoriteController::class, 'addFavorite']);
    Route::post('/comment', [CommentController::class, 'addComment']);
    Route::post('/follow', [FollowController::class, 'addFollow']);
    Route::get('/get_follower_following_favorite_count', [FollowController::class, 'getFollowingAndFollowerAndFavorite']);
    Route::post('/add_feed_back', [FeedBackController::class, 'addFeedback']);
    Route::post('/update_notification_setting', [UserSettingController::class, 'updateNotificationType']);
    Route::post('/update_fcm_token', [UpdateFcmTokenController::class, 'updateFcmToken']);
});

Route::group(['middleware' => 'auth:sanctum' , "prefix" => "admin"], function () {
    Route::post('/acceptAds', [AdminController::class, 'acceptAds']);
    Route::post('/delete_ads_for_admin', [AdminController::class, 'deleteAdsForAdmin']);
    Route::post('/delete_comment_admin', [AdminController::class, 'deleteCommentForAdmin']);
    Route::post('/block_user', [AdminController::class, 'blockUser']);
    Route::post('/star_ads', [AdminController::class, 'starAds']);
    Route::post('/update_priority', [AdminController::class, 'updatePriority']);
    Route::post('/delete_feed_back', [FeedBackController::class, 'deleteFeedback']);
    Route::post('/send_feedback_answer', [AdminController::class, 'sendFeedbackAnswer']);
});
