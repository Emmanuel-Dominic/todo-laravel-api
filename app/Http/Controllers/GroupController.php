<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function getAllGroups() {
        $messages = Group::get();
        $count = $messages->count();
        if ($count==0){
            return response()->json(['error' => 'No Groups'], 404);
        }
        return response()->json(["message" => $messages], 200);
    }

    public function createGroup(Request $request) {
        $message = new Group();
        $message["name"] = $request['name'];
        $message["purpose"] = $request['purpose'];
        $message["owner"] = Auth::id();
        $message->save();
        return response()->json([
            "success" => "group record created successfully",
            "message" => $message
        ], 201);
    }

    public function getGroup(int $groupId){
        $group = Group::findOrFail($groupId);
        return response()->json(['success' => $group], 200);
    }

}
