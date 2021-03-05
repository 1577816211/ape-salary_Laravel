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
    Route::post('/user/changePwd', 'UserController@changePwd');
    Route::post('/user/role', 'UserController@getRole');
});


\Illuminate\Support\Facades\Route::get('csv', 'IndexController@buildCsv');
