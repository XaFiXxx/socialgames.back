<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function createUserPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'group_id' => 'nullable|integer|exists:groups,id', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime|max:20000'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        $post = new Post();
        $post->content = $request->content;
        $post->user_id = $request->user_id;
    
        if ($request->has('group_id')) {
            $post->group_id = $request->group_id;
        }
    
        // Gestion des fichiers d'image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('img/posts/img'), $imageName);
            $post->image_path = 'img/posts/img/'.$imageName;
        }
    
        // Gestion des fichiers vidéo
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $videoName = time().'.'.$video->getClientOriginalExtension();
            $video->move(public_path('img/posts/video'), $videoName);
            $post->video_path = 'img/posts/video/'.$videoName;
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
