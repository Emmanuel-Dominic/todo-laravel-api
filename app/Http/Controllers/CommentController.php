<?php

namespace App\Http\Controllers;

use App\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function getAllComments(int $messageId) {
        $comments = Comment::where('message', $messageId)->get();
        $count = $comments->count();
        if ($count==0){
            return response()->json(['message' => 'No Comments'], 404);
        }
        return response($comments->toJson(JSON_PRETTY_PRINT), 200);
    }

    public function getComment(int $commentId) {
        if (Comment::where('id', $commentId)->exists()) {
            $comment = Comment::where('id', $commentId)->get()->toJson(JSON_PRETTY_PRINT);
            return response($comment, 200);
        } else {
            return response()->json([
                "message" => "Comment not found"
            ], 404);
        }
    }

    public function updateComment(Request $request, int $commentId) {
        if (Comment::where('id', $commentId)->exists()) {
            $comment = Comment::findOrFail($commentId);
            if ($comment->owner == Auth::id()){
                $comment->message = is_null($request['comment']) ? $comment->comment : $request['comment'];
                $comment->save();
                return response()->json([
                    "success" => "comment updated successfully",
                    "message" => $comment
                ], 200);
            }
            return response()->json([
                "message" => "sorry, you're not the author"
            ], 401);
        } else {
            return response()->json([
                "message" => "Comment not found"
            ], 404);
        }
    }

    public function deleteComment ($commentId) {
        if(Comment::where('id', $commentId)->exists()) {
            $comment = Comment::findOrFail($commentId);
            if ($comment->deleted_at!=null){
                return response()->json([
                    "error" => "comment already deleted"
                ], 400);
            }else{
                $comment->delete();
                return response()->json([
                    "success" => "Comment deleted successfully",
                    "message" => $comment
                ], 200);
            }
        } else {
            return response()->json([
                "message" => "Comment not found"
            ], 404);
        }
    }

    public function destroyComment ($commentId) {
        if(Comment::where('id', $commentId)->exists()) {
            $comment = Comment::findOrFail($commentId);
            if ($comment->owner == Auth::id()){
                $comment->forceDelete();
                return response()->json([
                    "message" => "Comment destroyed successfully"
                ], 202);
            }else{
                return response()->json([
                    "message" => "sorry, you're not the author"
                ], 401);
            }
        } else {
            return response()->json([
                "message" => "Comment not found"
            ], 404);
        }
    }


}
