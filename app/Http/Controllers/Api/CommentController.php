<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function commentsIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comments = Comment::where('commentable_type', $request->commentable_type)
            ->where('commentable_id', $request->commentable_id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }

    public function commentsStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'content' => 'required|string|min:2|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $model = $request->commentable_type::findOrFail($request->commentable_id);

        $parent = null;
        if ($request->parent_id) {
            $parent = Comment::findOrFail($request->parent_id);
        }

        $comment = $model->addComment(
            Auth::user(),
            $request->content,
            $parent
        );

        return response()->json([
            'comment' => $comment->load('user'),
            'message' => 'Commentaire ajouté avec succès',
        ], 201);
    }

    public function commentsLike(Comment $comment)
    {
        $result = $comment->toggleLike(Auth::user());

        return response()->json([
            'action' => $result['action'],
            'likes_count' => $comment->fresh()->likes_count,
            'dislikes_count' => $comment->fresh()->dislikes_count,
        ]);
    }

    public function commentsReport(Comment $comment, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $report = $comment->report(
            Auth::user(),
            $request->reason,
            $request->details
        );

        return response()->json([
            'message' => 'Commentaire signalé avec succès',
        ]);
    }
}
