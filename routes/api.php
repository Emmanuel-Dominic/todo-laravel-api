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
    Route::get('users/{userId}/messages', 'UserController@getUserChat')->middleware('verified');
    Route::post('users/{userId}/messages', 'UserController@createUserMessage')->middleware('verified');
    Route::patch('users/{userId}/update', 'UserController@updateUser')->middleware('verified');
    Route::delete('users/{userId}/delete', 'UserController@deleteUser')->middleware('verified');
    Route::get('users/{userId}/restore', 'UserController@restoreUser')->middleware('verified');
    Route::get('trash/users', 'UserController@trashedUsers')->middleware('verified');
    Route::get('records/users', 'UserController@userRecords')->middleware('verified');

    // Message Routes
    Route::get('messages', 'MessageController@getAllMessages')->middleware('verified');
//    Route::post('messages', 'MessageController@createMessage')->middleware('verified');
    Route::get('messages/{messageId}', 'MessageController@getMessage')->middleware('verified');
    Route::patch('messages/{messageId}', 'MessageController@updateMessage')->middleware('verified');
    Route::put('messages/{messageId}', 'MessageController@deleteMessage')->middleware('verified');
    Route::delete('messages/{messageId}', 'MessageController@destroyMessage')->middleware('verified');

    // Group Routes
    Route::get('groups', 'GroupController@getAllGroups')->middleware('verified');
    Route::post('groups', 'GroupController@createGroup')->middleware('verified');
    Route::get('groups/{groupId}', 'GroupController@getGroup')->middleware('verified');
    Route::get('groups/{groupId}/messages', 'GroupController@getGroupChat')->middleware('verified');
    Route::post('groups/{groupId}/messages', 'GroupController@createGroupMessage')->middleware('verified');
    Route::patch('groups/{groupId}', 'GroupController@updateGroup')->middleware('verified');
    Route::delete('groups/{groupId}', 'GroupController@deleteGroup')->middleware('verified');
});
