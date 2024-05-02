<?php

namespace App\Http\Controllers;
use App\Models\Post;

use Illuminate\Http\Request;

class homeController extends Controller
{
    public function home()
    {
        $posts = Post::orderBy('created_at', 'desc')->get(); // Tri des posts par date de création, du plus récent au plus ancien
        return response()->json($posts); // Renvoie les posts en JSON
    }
    

}
