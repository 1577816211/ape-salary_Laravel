<?php

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('cors')->group(function () {
    Route::post('/user/login', 'UserController@login');
    Route::post('/user/change-pwd', 'UserController@changePwd');
    Route::post('/user/add-staff', 'UserController@addStaff');
    Route::post('/user/staff-list', 'UserController@staffList');
});

