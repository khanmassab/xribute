<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\TeamsController;
use App\Http\Controllers\Api\V2\BusinessController;

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/users', function () {
            return response()->json(['message' => 'This is the new implementation of the /users endpoint for version 2']);
        });

    Route::post('add-business-address-and-contact', [BusinessController::class, 'addressesAndContacts']);
    Route::post('add-business-handles', [BusinessController::class, 'addBusinessHandle']);
    Route::get('get-business', [BusinessController::class, 'getBusinessSummary']);
    Route::get('get-business/{businessId}', [BusinessController::class, 'getBusinessDetail']);

    Route::match(['post', 'put'], 'business/create_business/details/{id?}', [BusinessController::class, 'createBusiness']);
    Route::get('business/fetch/business', [BusinessController::class, 'getAllBusinesses']);
    Route::get('business/fetch/shareholders', [BusinessController::class, 'getAllBusinessShares']);
    Route::match(['post', 'put'], 'business/create_business/shareholder/{id?}', [BusinessController::class, 'businessShares']);
    Route::match(['post', 'put'], 'business/create_business/management/{id?}', [BusinessController::class, 'businessManagement']);
    Route::match(['post', 'put'], 'business/create_business/address_and_contact/{id?}', [BusinessController::class, 'businessAddressContact']);
    Route::match(['post', 'put'], 'business/create_business/platform/{id?}', [BusinessController::class, 'businessPlatform']);
    Route::match(['post', 'put'], 'business/create_business/branch/{id?}', [BusinessController::class, 'businessBranch']);

    Route::post('business/active-deactive/{id}', [BusinessController::class, 'toggleBusinessStatus']);

    Route::delete('business/delete/business/{id}', [BusinessController::class, 'deleteBusiness']);
    Route::delete('business/delete/shareholder/{id}', [BusinessController::class, 'deleteShareholder']);
    Route::delete('business/delete/address_and_contact/{id}', [BusinessController::class, 'deleteAddressAndContact']);
    Route::delete('business/delete/management/{id}', [BusinessController::class, 'deleteManagement']);
    Route::delete('business/delete/platform/{id}', [BusinessController::class, 'deletePlatform']);
    Route::delete('business/delete/branch/{id}', [BusinessController::class, 'deleteBranch']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'teams'], function () {
    Route::post('/add', [TeamsController::class, 'createTeam']);
    Route::post('/invite_user', [TeamsController::class, 'addUser']);
    Route::post('/get_complete_team_data/{id}', [TeamsController::class, 'getCompleteTeam']);
    Route::get('/team_name/{id}', [TeamsController::class, 'getTeamNames']);
    Route::get('/invited_users/{id}', [TeamsController::class, 'getInvitedUsers']);
    Route::delete('/delete_invitation/{id}', [TeamsController::class, 'deleteInvitation']);
    Route::post('/add_to_team', [TeamsController::class, 'manageUser']);

    Route::post('/resend_invitation/{id}', [TeamsController::class, 'resendInvitation']);
    Route::get('/get_roles', [TeamsController::class, 'getRoles']);
});

