<?php

namespace App\Http\Controllers;

use App\Group;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function getAllGroups() {
        $groups = Group::get();
        $count = $groups->count();
        if ($count==0){
            return response()->json(['error' => 'No Groups'], 404);
        }
        return response()->json(["message" => $groups], 200);
    }

    public function createGroup(Request $request) {
        $group = new Group();
        $group["name"] = $request['name'];
        $group["purpose"] = $request['purpose'];
        $group["owner"] = Auth::id();
        $group->save();
        return response()->json([
            "success" => "group record created successfully",
            "message" => $group
        ], 201);
    }

    public function getGroup(int $groupId){
        $group = Group::findOrFail($groupId);
        return response()->json(['success' => $group], 200);
    }

    public function createGroupMessage(Request $request, int $groupId) {
        $message = new Message;
        $message['message'] = $request['message'];
        $message['comment_on'] = null;
        $message['user'] = null;
        if ($request['status']=='comment'){
            $messaging = Message::where('id', $request['comment_on'])->get();
            $message['comment_on'] = $request['comment_on'];
        }
        $message['group'] = $groupId;
        $message['owner'] = Auth::id();
        $group = Group::findOrFail($groupId);
        $message->save();
        return response()->json([
            "success" => "message sent to {$group->name}",
            "message" => $message
        ], 201);
    }


    public function getGroupChat($groupId) {
        $message = Message::where('group', $groupId)->join(
            'users', 'messages.owner', '=', 'users.id')->select(
                'messages.*', 'users.username')->get();
        if (Message::where('group', $groupId)->exists()) {
            return response()->json($message, 200);
        } else {
            return response()->json([
                "message" => "No messages"
            ], 404);
        }
    }

    public function updateGroup(Request $request, int $groupId) {
        if (Group::where('id', $groupId)->exists()) {
            $group = Group::findOrFail($groupId);
            if ($group->owner != Auth::id()){
                return response()->json([
                    "message" => "sorry, contact the group admin"
                ], 401);
            }else{
                $group->name = is_null($request['name']) ? $group->name : $request['name'];
                $group->purpose = is_null($request['purpose']) ? $group->purpose : $request['purpose'];
                $group->save();
                return response()->json([
                    "success" => "group updated successfully",
                    "message" => $group
                ], 200);
            }
        } else {
            return response()->json([
                "message" => "Group not found"
            ], 404);
        }
    }

    public function deleteGroup ($groupId) {
        if(Group::where('id', $groupId)->exists()) {
            $group = Group::findOrFail($groupId);
            if ($group->owner!=Auth::id()){
                return response()->json([
                    "error" => "unauthorized, contact the group super admin"
                ], 401);
            }else{
                $group->forceDelete();
                return response()->json([
                    "success" => "Group deleted successfully",
                    "message" => $group
                ], 200);
            }
        } else {
            return response()->json([
                "message" => "Group not found"
            ], 404);
        }
    }

}
