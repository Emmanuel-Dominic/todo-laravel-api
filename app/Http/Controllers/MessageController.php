<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getAllMessages() {
        $messages = Message::get();
        $count = $messages->count();
        if ($count==0){
            return response()->json(['message' => 'No Messages'], 404);
        }
        return response($messages->toJson(JSON_PRETTY_PRINT), 200);
    }

    public function createMessage(Request $request) {
        $message = new Message;
        $message['message'] = $request['message'];
        $message['owner'] = Auth::id();
        $message->save();
        return response()->json([
            "success" => "message record created",
            "message" => $message
        ], 201);
    }

    public function getMessage($messageId) {
        if (Message::where('id', $messageId)->exists()) {
            $message = Message::where('id', $messageId)->get()->toJson(JSON_PRETTY_PRINT);
            return response($message, 200);
        } else {
            return response()->json([
                "message" => "Message not found"
            ], 404);
        }
    }

}
