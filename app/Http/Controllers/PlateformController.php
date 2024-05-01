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
}
