<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

 Route::get('/', function () {
     return view('welcome');
 });

 Route::get('/join_team', 'App\Http\Controllers\Api\V2\TeamsController@acceptInvitation');
// Route::get('/signup', function () {
//     return view('register');
// });

// Route::get('/login', function () {
//     return view('login');
// });

