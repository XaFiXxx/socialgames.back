<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;

class PlateformController extends Controller
{
    public function index()
    {
        $platforms = Platform::all(); 
        return response()->json($platforms);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $platform = Platform::create([
            'name' => $request->name,
        ]);

        return response()->json($platform, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $platform = Platform::findOrFail($id);
        $platform->update([
            'name' => $request->name,
        ]);

        return response()->json($platform);
    }

    public function delete($id)
    {
        $platform = Platform::findOrFail($id);
        $platform->delete();

        return response()->json(['message' => 'Platform deleted successfully']);
    }
}
