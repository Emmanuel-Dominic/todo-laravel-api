<?php

namespace App\Http\Controllers;
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
        if ($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['access_token'] = $user->createToken($this->secrete_key)->accessToken;
        $success['message'] = 'User successfully Registered';
        $success['username'] = $user->username;
        $success['email'] = $user->email;
        return response()->json(['success' => $success], 201);
    }

    /**
     * User login api controller, verifies user credentials
     *
     * @return JsonResponse
     */
    public function userLogin()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['access_token'] = $user->createToken($this->secrete_key)->accessToken;
            $success['message'] = 'LoggedIn successfully';
            $success['username'] = $user->username;
            $success['email'] = $user->email;
            return response()->json(['success' => $success], 200);
        }else{
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
        if ($count==0){
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

}
