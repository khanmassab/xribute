<?php

use App\Http\Controllers\Api\Chat\PrivateChatController;
use App\Http\Controllers\Api\Chat\ChatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\BusinessProfileController;
// use App\Http\Controllers\Api\V2\User\ProfileController as ProfileControllerV2;
// use App\Http\Controllers\Api\V2\User\ProfileControllerV2;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
*/

Route::group(['middleware' => 'auth:api'], function(){
    Route::get('/home', [HomeController::class, 'home']);

    Route::post('/profile/nationality', [ProfileController::class, 'nationalityPost']);
    Route::post('/profile/residence', [ProfileController::class, 'residencePost']);
    Route::post('/profile/account_update', [ProfileController::class, 'profilePost']);

    Route::get('/profile/account_get', [ProfileController::class, 'profileGet']);

    Route::post('/profile/upload_picture',[ProfileController::class, 'imageStore']);
    Route::post('/profile/update_notification_settings',[ProfileController::class, 'updateNotificationSetting']);
    // Route::post('/profile/account_update',[ProfileController::class, 'updateAccount']);
    Route::post('/profile/address_update',[ProfileController::class, 'updateAddress']);
    Route::post('/profile/update_contact_details',[ProfileController::class, 'updateContactDetails']);
    Route::post('/profile/update_password',[ProfileController::class, 'updatePassword']);
    Route::get('/profile/get_user_data',[ProfileController::class, 'getUserData']);
    Route::get('/profile/get_user_data_phone',[ProfileController::class, 'getUserDataApps']);

    //Buiness Profile
    Route::post('/create_business_profile',[BusinessProfileController::class, 'createBusinessProfile']);
    

    Route::post('/delete_business_profile',[BusinessProfileController::class, 'deleteBusinessProfile']);
    Route::post('/create_business_categories',[BusinessProfileController::class, 'createBusinessCategories']);
    Route::post('/create_business_product',[BusinessProfileController::class, 'createBusinessProducts']);
    Route::post('/update_business_product',[BusinessProfileController::class, 'UpdateBusinessProduct']);
    Route::get('/business_category',[BusinessProfileController::class, 'businessCategory']);
    Route::get('/get_business_product',[BusinessProfileController::class, 'getBusinessProduct']);
    Route::get('/get_single_product',[BusinessProfileController::class, 'getSingleProduct']);
    Route::get('/get_all_products',[BusinessProfileController::class, 'getAllProducts']);
    Route::post('/active_deactive_product',[BusinessProfileController::class, 'ActiveDeActiveProduct']);
    Route::get('/get_cities_list',[BusinessProfileController::class, 'citiesList']);
    Route::get('/get_countries_list',[BusinessProfileController::class, 'countriesList']);
    Route::get('/get_business_profiles',[BusinessProfileController::class, 'businessProfilesList']);
    Route::get('/get_business_single_profiles',[BusinessProfileController::class, 'businessProfile']);

    Route::post('/chat-user-list', [ChatsController::class, 'chatList']);
    Route::post('/chat', [ChatsController::class, 'index']);
    Route::get('/messages', [ChatsController::class, 'fetchMessages']);
    Route::post('/messages', [ChatsController::class, 'sendMessage']);
    Route::post('add-quotation', [ChatsController::class, 'addQuotation']);

    Route::get('private-chat', [PrivateChatController::class, 'index']);
    Route::post('private-chat', [PrivateChatController::class, 'store']);
    Route::get('fetch-private-chat/{chatroom}', [PrivateChatController::class, 'get']);

});

 //Force JSON RESPONSE IN APIs
Route::group([],function () {
    // public routes
    Route::post('/register', [AuthController::class, 'register'])->name('register.api');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout.api');
    Route::post('/login',[AuthController::class, 'login'])->name('login.api');
    Route::post('/verify_email',[AuthController::class, 'verifyEmail']);
    Route::post('/resend_email',[AuthController::class, 'resendEmail']);
    Route::post('/password_reset_email',[AuthController::class, 'forgetPasswordEmail']);
    Route::post('/enter_verification_code',[AuthController::class, 'enterVerificationCode']);
    Route::post('/update_forgotten_password',[AuthController::class, 'forgetPasswordVerifyCode']);

    Route::get('/get_user_types',[AuthController::class, 'getUserTypes']);

});

// Route::group(['prefix' => 'v2', 'middleware' => 'auth:api'], function(){

// });
