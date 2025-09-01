<?php

namespace App\Http\Controllers;

class ImageController extends Controller
{
    public function __invoke($image)
    {
        return response()->download(resource_path('images/'.$image));
    }
}
