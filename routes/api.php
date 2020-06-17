<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/register', 'UserController@userRegister');
Route::post('/login', 'UserController@userLogin');

Route::group(['middleware' => 'auth:api'], function () {
    // User Routes
    Route::get('users', 'UserController@getAllUsers')->middleware('verified');
    Route::get('users/{userId}', 'UserController@getUser')->middleware('verified');
    Route::patch('users/{userId}/update', 'UserController@updateUser')->middleware('verified');
    Route::delete('users/{userId}/delete', 'UserController@deleteUser')->middleware('verified');
    Route::get('users/{userId}/restore', 'UserController@restoreUser')->middleware('verified');
    Route::get('trash/users', 'UserController@trashedUsers')->middleware('verified');
    Route::get('records/users', 'UserController@userRecords')->middleware('verified');

    // Message Routes
    Route::get('messages', 'MessageController@getAllMessages')->middleware('verified');
    Route::post('messages', 'MessageController@createMessage')->middleware('verified');
    Route::get('messages/{messageId}', 'MessageController@getMessage')->middleware('verified');
    Route::patch('messages/{messageId}', 'MessageController@updateMessage')->middleware('verified');
});
