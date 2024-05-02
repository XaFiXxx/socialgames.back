<?php

namespace App\Http\Controllers;
use App\Models\Post;

use Illuminate\Http\Request;

class homeController extends Controller
{
    public function home()
{
    $posts = Post::all();
    return response()->json($posts); // Renvoie les posts en JSON
}

}
