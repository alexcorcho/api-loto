<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class WordpressApiController extends Controller
{
    private $wpApiUrl;
    private $token;

    public function __construct()
    {
        $this->wpApiUrl = config('services.wordpress.api_url');
        $this->token = config('services.wordpress.api_token');
    }

// Obtener todos los usuarios
public function getUsers()
{
    $response = Http::withToken($this->token)->get($this->wpApiUrl);
    return response()->json($response->json(), $response->status());
}


// Obtener un usuario específico
public function show($id)
    {
        $response = Http::withToken($this->token)->get("{$this->wpApiUrl}/{$id}");
        return response()->json($response->json(), $response->status());
    }
}