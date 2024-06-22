<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $posts = Post::where('user_id', $credentials->user_id)->with('user', 'category')->get();

        $postData = [];

        // Iterate through each post to extract necessary data
        foreach ($posts as $post) {
            $postData[] = [
                'post_id' => $post->id,
                'user' => $post->user->name,
                'category' => $post->category->name, // Assuming category relationship is loaded correctly
                'title' => $post->title,
                'content' => $post->content,
            ];
        }

        return response()->json([
            'data' => $postData // Return all posts data as JSON
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'category' => 'required|exists:categories,id',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category,
            'user_id' => $credentials->user_id
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'data' => $post
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $post = Post::where(['user_id' => $credentials->user_id, 'id' => $id])->with('user', 'category', 'comments.user')->first();

        if (!$post) {
            return response()->json(['error' => "Post not found"], 401);
        }


        $postData[] = [
            'post_id' => $post->id,
            'user' => $post->user->name,
            'category' => $post->category->name, // Assuming category relationship is loaded correctly
            'title' => $post->title,
            'content' => $post->content,
            'comments' => $post->comments->map(function ($comment) {
                return [
                    'comment_id' => $comment->id,
                    'comment_user' => $comment->user->name,
                    'comment_content' => $comment->content,
                ];
            })
        ];

        return response()->json([
            'data' => $postData // Return all posts data as JSON
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'category' => 'required|exists:categories,id'
        ]);

        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $post = Post::where(['id' => $id, 'user_id' => $credentials->user_id])->first();

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 401);
        }

        $postData = [
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category
        ];
        $post->update($postData);

        return response()->json(['message' => 'Post update successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $post = Post::where(['id' => $id, 'user_id' => $credentials->user_id])->first();

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 401);
        }

        $post->delete();

        return response()->json(['message' => 'Post delete successfully'], 200);
    }
}
