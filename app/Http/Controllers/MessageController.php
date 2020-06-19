<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getAllMessages() {
        $messages = Message::orderBy('created_at', 'desc')->get();
        $count = $messages->count();
        if ($count==0){
            return response()->json(['message' => 'No Messages'], 404);
        }
        return response($messages->toJson(JSON_PRETTY_PRINT), 200);
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

    public function updateMessage(Request $request, $messageId) {
        if (Message::where('id', $messageId)->exists()) {
            $message = Message::findOrFail($messageId);
            if ($message->owner == Auth::id()){
                $message->message = is_null($request['message']) ? $message->message : $request['message'];
                $message->save();
                return response()->json([
                    "success" => "message updated successfully",
                    "message" => $message
                ], 200);
            }
            return response()->json([
                "message" => "unauthorized, this message doesn't belong to you"
            ], 401);
        } else {
            return response()->json([
                "message" => "Message not found"
            ], 404);
        }
    }

    public function deleteMessage ($messageId) {
        if(Message::where('id', $messageId)->exists()) {
            $message = Message::findOrFail($messageId);
            if ($message->deleted_at!=null){
                return response()->json([
                    "error" => "message already deleted"
                ], 400);
            }else{
            $message->delete();
            return response()->json([
                "success" => "Message deleted successfully",
                "message" => $message
            ], 200);
            }
        } else {
            return response()->json([
                "message" => "Message not found"
            ], 404);
        }
    }

    public function destroyMessage ($messageId) {
        if(Message::where('id', $messageId)->exists()) {
            $message = Message::findOrFail($messageId);
            if ($message->owner == Auth::id()){
                $message->forceDelete();
                return response()->json([
                    "message" => "Message destroyed successfully"
                ], 202);
            }else{
            return response()->json([
                "message" => "sorry, you're not the author"
            ], 401);
            }
        } else {
            return response()->json([
                "message" => "Message not found"
            ], 404);
        }
    }

}
