<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

}
