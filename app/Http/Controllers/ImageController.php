<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __invoke($image)
    {
        return response()->download(resource_path('images/' . $image));
    }
}
