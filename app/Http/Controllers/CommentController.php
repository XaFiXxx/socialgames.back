<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Méthode pour créer un commentaire
    public function createComment(Request $request, $postId)
    {
        // Valider les données du commentaire
        $request->validate([
            'content' => 'required|string',
        ]);

        // Trouver le post
        $post = Post::findOrFail($postId);

        // Créer le commentaire
        $comment = new Comment([
            'content' => $request->content,
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        // Enregistrer le commentaire
        $comment->save();

        // Charger l'utilisateur du commentaire
        $comment->load('user');

        // Retourner une réponse JSON
        return response()->json(['message' => 'Comment added successfully', 'comment' => $comment]);
    }
}
