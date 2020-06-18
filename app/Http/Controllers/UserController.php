<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;


class UserController extends Controller
{
    //
    private $secrete_key = 'hsw67wb%^&*$#xnjksnjcnsjknsjsjnsjncsjjssnjn';

    /**
     * User Registration api controller for creating a new users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function userRegister(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'username' => 'required|unique:users|string|max:25',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['access_token'] = $user->createToken($this->secrete_key)->accessToken;
        $success['message'] = 'User successfully Registered';
        $success['username'] = $user->username;
        $success['email'] = $user->email;
        $success['id'] = $user->id;
        return response()->json(['success' => $success], 201);
    }

    /**
     * User login api controller, verifies user credentials
     *
     * @return JsonResponse
     */
    public function userLogin()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['access_token'] = $user->createToken($this->secrete_key)->accessToken;
            $success['message'] = 'LoggedIn successfully';
            $success['username'] = $user->username;
            $success['email'] = $user->email;
            $success['id'] = $user->id;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorised, invalid credentials provided'], 401);
        }
    }

    /**
     * Display all users api controller resource.
     *
     * @return JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::all();
        $count = $users->count();
        if ($count == 0) {
            return response()->json(['error' => 'No users found'], 404);
        }
        $success['data'] = $users;
        $success['total'] = $count;
        return response()->json(['success' => $success], 200);
    }

    /**
     * Display single user api controller resource.
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function getUser(int $userId)
    {
        $user = User::findOrFail($userId);
        return response()->json(['success' => $user], 200);
    }

    public function createUserMessage(Request $request, int $userId) {
        $message = new Message;
        $message['message'] = $request['message'];
        $message['user'] = $userId;
        $message['group'] = null;
        $message['owner'] = Auth::id();
        $user = User::findOrFail($userId);
        $message->save();
        return response()->json([
            "success" => "message sent to {$user->username}",
            "message" => $message
        ], 201);
    }

    public function getUserChat($userId) {
        if (Message::where('user', $userId)->exists()) {
            $message = Message::where('user', $userId)->get();
            return response()->json($message, 200);
        } else {
            return response()->json([
                "message" => "No messages"
            ], 404);
        }
    }

    /**
     * Update user details api controller for a specified resource.
     *
     * @param Request $request
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function updateUser(Request $request, int $userId)
    {
        if (User::where('id', $userId)->exists()) {
            $user = User::findOrFail($userId);
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'string|max:255',
                'password' => '',
                'confirm_password' => 'same:password'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $user->name = is_null($input['name']) ? $user->name : $input['name'];
            $user->password = is_null($input['password']) ? $user->password : bcrypt($input['password']);
            $user->save();
            return response()->json([
                "message" => "user record updated successfully", "data" => $user
            ], 200);
        }else{
            return response()->json(['error' => 'user not found'], 404);
        }

    }


    /**
     * Soft deletes a specified user api controller resource.
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function deleteUser(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        $success['message'] = 'user record deleted successfully';
        $success['data'] = User::onlyTrashed()->find($userId);
        return response()->json(['success' => $success], 200);
    }


    /**
     * Restores a deleted user api controller record from trash
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function restoreUser(int $userId){
        $user = User::onlyTrashed()->findOrFail($userId);
        $user->restore();
        $success['message'] = 'user record restored successfully';
        $success['data'] = User::find($userId);
        return response()->json(['success' => $success], 200);
    }

    /**
     * Display all trashed users api controller resource.
     *
     * @return JsonResponse
     */
    public function trashedUsers()
    {
        $trash_users = User::onlyTrashed()->get();
        $count = $trash_users->count();
        if ($count == 0) {
            return response()->json(['error' => 'No trashed users found'], 404);
        }
        $success['data'] = $trash_users;
        $success['total'] = $count;
        return response()->json(['success' => $success], 200);
    }


    /**
     * Display all registered users historical api controller records, both activate and deactivated users
     *
     * @return JsonResponse
     */
    public function userRecords(){
        $users_history = User::withTrashed()->orderBy('created_at', 'desc')->get();
        $count = $users_history->count();
        if ($count == 0) {
            return response()->json(['error' => 'No historical users found'], 404);
        }
        $success['message'] = 'Historical user records';
        $success['data'] = $users_history;
        $success['total'] = $count;
        return response()->json(['success' => $success], 200);
    }
}
