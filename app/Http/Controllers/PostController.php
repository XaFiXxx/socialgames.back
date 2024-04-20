<?php

namespace App\Http\Controllers;

use App\Models\Post;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function createUserPost(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $post = new Post();
        $post->content = $request->content;
        $post->user_id = $request->user_id;
        $post->save();

        return response()->json(['message' => 'Post créé avec succès!', 'post' => $post], 201);
    }
}
