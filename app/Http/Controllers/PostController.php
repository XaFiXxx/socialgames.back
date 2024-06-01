<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function createUserPost(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'group_id' => 'nullable|integer|exists:groups,id', // Validation optionnelle pour l'ID du groupe
        ]);

        $post = new Post();
        $post->content = $request->content;
        $post->user_id = $request->user_id;
        
        // Si un group_id est fourni, l'ajouter au post
        if ($request->has('group_id')) {
            $post->group_id = $request->group_id;
        }
        
        $post->save();

        return response()->json(['message' => 'Post créé avec succès!', 'post' => $post], 201);
    }


    public function likePost(Request $request, $id)
    {
        $user = $request->user();
        $like = PostLike::where('post_id', $id)->where('user_id', $user->id)->first();

        if ($like) {
            // Unlike the post
            $like->delete();
            return response()->json(['message' => 'Post unliked']);
        } else {
            // Like the post
            PostLike::create([
                'post_id' => $id,
                'user_id' => $user->id,
            ]);
            return response()->json(['message' => 'Post liked']);
        }
    }
}
