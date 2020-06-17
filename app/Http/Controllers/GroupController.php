<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;

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

}
