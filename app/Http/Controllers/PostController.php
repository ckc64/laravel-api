<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function addNewPost(Request $request)
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
            $post->title = $request->title;
            $post->content = $request->content;
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
}
