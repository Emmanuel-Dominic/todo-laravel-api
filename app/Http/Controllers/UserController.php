<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Validator;


class UserController extends Controller
{
    //
    private $secrete_key = 'hsw67wb%^&*$#xnjksnjcnsjknsjsjnsjncsjjssnjn';


    public function uploadProfileImage($image)
    {
      $maxsize = 2097152;
      $allowed = array('jpg', 'jpeg', 'gif', 'png');

        // $image_name = $image['name'];
       $image_name = $image->getClientOriginalName();
        // $image_extn = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
       $image_extn = $image->getClientOriginalExtension();
//         $image_temp = $_FILES['profile']['tmp_name'];
       $image_size = $image->getSize();
        // $image_mime = $image->getClientMimeType();

        if (in_array($image_extn, $allowed) === false) {
            return response()->json(['error' => 'Invalid image file provided', 'allowed_types' => $allowed], 400);
        }
        if ($image_size >= $maxsize) {
            return response()->json(['error' => 'File too large. File must be less than 2 megabytes.'], 400);
        }
        if ($image->isValid()) {
            $rdm = uniqid(5);
            $name = $rdm . '-' . date('mdYHis');
            $filename = $name . '.' . $image_extn;
            // Storage::disk('profile')->put($filename, File::get($image));
            $image->move(public_path().'/images/profile/', $filename);
            return $filename;
        }
    }

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
        $image = $request->file('avatar');
        $validator = Validator::make($input, [
            'username' => 'required|unique:users|string|max:25',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ]);
        $avatar = '';
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if ($request->hasfile('avatar')) {
            foreach ($image as $data) {
            $avatar = $this->uploadProfileImage($data);
          }
        }else{
            $avatar = '55ef0f19b6a031-06222020175955.jpeg';
        }
        $user = User::make([
              'username' => $input['username'],
              'name' => $input['name'],
              'email' => $input['email'],
              'password' => bcrypt($input['password']),
              'avatar' => $avatar,
        ]);
        $user->save();
        $success['access_token'] = $user->createToken($this->secrete_key)->accessToken;
        $success['message'] = 'User successfully Registered';
        $success['avatar'] = $user->getImageUrl($avatar);
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

    public function createUserMessage(Request $request, int $userId)
    {
        $message = new Message;
        $message['message'] = $request['message'];
        $message['comment_on'] = null;
        $message['user'] = null;
        if ($request['status'] == 'comment') {
            $messaging = Message::where('id', $request['comment_on'])->get();
            $message['comment_on'] = $request['comment_on'];
        }
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

    public function getUserChat($userId)
    {
        if (Message::where('user', $userId)->where('owner', Auth::id())->orWhere(function ($query) use ($userId) {
            $query->where('user', Auth::id())->where('owner', $userId);
        })->exists()) {
            $message = Message::where('user', $userId)->where('owner', Auth::id())->orWhere(
                function ($query) use ($userId) {
                    $query->where('user', Auth::id())->where('owner', $userId);
                })->join('users', 'messages.owner', '=', 'users.id')->select('messages.*', 'users.username')->get();
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
        } else {
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
    public function restoreUser(int $userId)
    {
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
    public function userRecords()
    {
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
