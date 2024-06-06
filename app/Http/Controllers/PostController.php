<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif|max:2048',
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
            $imagePath = $request->file('image')->store('img/posts/img', 'public');
            $post->image_path = 'storage/' . $imagePath;
        }
    
        // Gestion des fichiers vidéo
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('img/posts/video', 'public');
            $post->video_path = 'storage/' . $videoPath;
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

    // ------------------- ROUTES FOR DASHBOARD ------------------- //

    public function index()
    {
        // Récupérer tous les posts avec les informations de l'utilisateur associé
        $posts = Post::with('user', 'group')->get();

        return response()->json($posts);
    }
    

    public function updateDashboard(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'group_id' => 'nullable|integer|exists:groups,id',
            'image' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif|max:2048',
            'video' => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime|max:20000'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post non trouvé.'], 404);
        }

        $post->content = $request->input('content');
        $post->user_id = $request->input('user_id');
        $post->group_id = $request->input('group_id');

        // Gestion des fichiers d'image
        if ($request->hasFile('image')) {
            $oldImagePath = str_replace('storage/', '', $post->image_path);
            if ($post->image_path && Storage::disk('public')->exists($oldImagePath) && !in_array($post->image_path, ['storage/img/users/defaultUser.webp'])) {
                Storage::disk('public')->delete($oldImagePath);
            }
            $imagePath = $request->file('image')->store('img/posts/img', 'public');
            $post->image_path = 'storage/' . $imagePath;
        }

        // Gestion des fichiers vidéo
        if ($request->hasFile('video')) {
            $oldVideoPath = str_replace('storage/', '', $post->video_path);
            if ($post->video_path && Storage::disk('public')->exists($oldVideoPath)) {
                Storage::disk('public')->delete($oldVideoPath);
            }
            $videoPath = $request->file('video')->store('img/posts/video', 'public');
            $post->video_path = 'storage/' . $videoPath;
        }

        $post->save();

        return response()->json(['post' => $post, 'message' => 'Post mis à jour avec succès!'], 200);
    }


    public function deleteDashboard(Request $request, $id)
    {
        $user = Auth::user();
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post non trouvé.'], 404);
        }

        if ($post->user_id != $user->id) {
            return response()->json(['message' => 'Non autorisé à supprimer ce post.'], 403);
        }

        // Supprimer l'image associée si elle existe
        if ($post->image_path && Storage::disk('public')->exists(str_replace('storage/', '', $post->image_path))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $post->image_path));
        }

        // Supprimer la vidéo associée si elle existe
        if ($post->video_path && Storage::disk('public')->exists(str_replace('storage/', '', $post->video_path))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $post->video_path));
        }

        $post->delete();
        return response()->json(['message' => 'Post supprimé avec succès!'], 200);
    }

}
