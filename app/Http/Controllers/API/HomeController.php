<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $posts = Post::with('user', 'category')->get();

        $postData = [];
        foreach ($posts as $post) {
            $postData[] = [
                'id' => $post->id,
                'author' => $post->user->name,
                'category' => $post->category->name,
                'title' => $post->title,
                'content' => $post->content
            ];
        }

        return response()->json(['data' => $postData]);
    }

    public function Post(string $id)
    {
        $posts = Post::where('id', $id)->with('user', 'category', 'comments.user')->get();

        $postData = [];
        foreach ($posts as $post) {
            $postData[] = [
                'id' => $post->id,
                'author' => $post->user->name,
                'category' => $post->category->name,
                'title' => $post->title,
                'content' => $post->content,
                'comment' => $post->comments->map(function ($comment) {
                    return [
                        'comment_id' => $comment->id,
                        'comment_user' => $comment->user->name,
                        'comment_content' => $comment->content,
                    ];
                })
            ];
        }

        return response()->json(['data' => $postData]);
    }

    public function Comment(Request $request, $id)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Please log for posting comment']);
        }

        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $request->validate([
            'comment' => 'required|string'
        ]);

        $comment = Comment::create([
            'post_id' => $id,
            'user_id' => $credentials->user_id,
            'content' => $request->comment
        ]);

        return response()->json([
            'message' => 'Comment posted',
            'data' => $comment
        ]);
    }

    public function updateComment(Request $request, string $id)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Please log in to update the comment'], 401);
        }

        $key = env('JWT_SECRET');
        try {
            $credentials = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $comment = Comment::where('id', $id)->first();

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($comment->user_id != $credentials->user_id) {
            return response()->json(['message' => 'You can only update your own comment'], 403);
        }

        $request->validate([
            'comment' => 'required|string'
        ]);

        $comment->content = $request->comment;
        $comment->save();

        return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment], 200);
    }

    public function deleteComment(Request $request, string $id)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Please log in to update the comment'], 401);
        }

        $key = env('JWT_SECRET');
        try {
            $credentials = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $comment = Comment::where('id', $id)->first();

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($comment->user_id != $credentials->user_id) {
            return response()->json(['message' => 'You can only delete your own comment'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }
}
