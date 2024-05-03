<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('username'); // Récupère la chaîne de recherche de l'URL

        $users = User::where('username', 'LIKE', '%' . $query . '%')->get();

        return response()->json($users);
    }
}
