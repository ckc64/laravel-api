<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function addNewPost(Request $request) : JsonResponse
    {
        $data = json_decode($request->getContent(), true); //ensuring only the body is processed


        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($data ?? [], [
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 403);
        }
        try {
            $post = new Post();
            $post->title = $data['title'];
            $post->content = $data['content'];
            $post->user_id = Auth::id();
            $post->save();

            return response()->json([
                'message' => 'Post Successful',
                'post' => $post,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deletePost(Request $request) : JsonResponse
    {
        $data = json_decode($request->getContent(), true); //ensuring only the body is processed

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = Validator::make($data ?? [], [
            'id' => 'required|integer',
        ]);

        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }

        try {
            $post = Post::find($data['id']);
            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            $post->delete();

            return response()->json(['message' => 'Post deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePost(Request $request) : JsonResponse
    {
        $data = json_decode($request->getContent(), true); // ensuring only the body is processed

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = Validator::make($data ?? [], [
            'id' => 'required|integer',
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }

        try {
            $post = Post::find($data['id']);
            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            $post->title = $data['title'];
            $post->content = $data['content'];
            $post->save();

            return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    }
