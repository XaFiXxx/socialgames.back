<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    // Méthode pour obtenir tous les genres
    public function index()
    {
        $genres = Genre::all();
        return response()->json($genres);
    }

    // Méthode pour obtenir un genre spécifique (facultatif)
    public function show($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'Genre non trouvé.'], 404);
        }

        return response()->json($genre);
    }

    // Méthode pour créer un nouveau genre (facultatif)
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $genre = new Genre();
        $genre->name = $request->name;
        $genre->save();

        return response()->json(['message' => 'Genre créé avec succès.', 'genre' => $genre], 201);
    }

    // Méthode pour mettre à jour un genre (facultatif)
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'Genre non trouvé.'], 404);
        }

        $genre->name = $request->name;
        $genre->save();

        return response()->json(['message' => 'Genre mis à jour avec succès.', 'genre' => $genre]);
    }

    // Méthode pour supprimer un genre (facultatif)
    public function delete($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'Genre non trouvé.'], 404);
        }

        $genre->delete();

        return response()->json(['message' => 'Genre supprimé avec succès.']);
    }
}
