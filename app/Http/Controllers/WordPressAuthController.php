<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressAuthController extends Controller
{
public function getToken(Request $request)
{
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        try {
            $response = Http::post(config('services.wordpress.api_url') . '/jwt-auth/v1/token', [
                'username' => $request->input('username'),
                'password' => $request->input('password')
            ]);
    
            if (!$response->successful()) {
                return response()->json([
                    'error' => 'No se pudo obtener el token de WordPress',
                    'details' => $response->json()
                ], $response->status());
            }
    
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno en el servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createUser(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'email' => 'required|email',
        'password' => 'required|string',
        'role' => 'required|string' // Ejemplo: 'subscriber', 'editor', 'administrator'
    ]);

    try {
        $token = $request->header('Authorization'); // Capturamos el token enviado en la cabecera

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        // Hacemos la solicitud a WordPress
        $response = Http::withHeaders([
            'Authorization' => $token, // Enviamos el token JWT de autenticación
            'Content-Type' => 'application/json'
        ])->post(config('services.wordpress.api_url') . '/wp/v2/users', [
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'roles' => [$request->input('role')]
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudo crear el usuario en WordPress',
                'details' => $response->json()
            ], $response->status());
        }

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $response->json()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno en el servidor',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function deleteUser(Request $request, $id)
{
    $token = $request->header('Authorization'); // Capturamos el token enviado en la cabecera

    if (!$token) {
        return response()->json(['error' => 'Token no proporcionado'], 401);
    }

    try {
        // Hacemos la solicitud de eliminación al endpoint de WordPress
        $response = Http::withHeaders([
            'Authorization' => $token, // Enviamos el token JWT de autenticación
            'Content-Type' => 'application/json'
        ])->delete(config('services.wordpress.api_url') . '/wp/v2/users/' . $id, [
            'force' => true,  // Forzamos la eliminación
            'reassign' => 1    // Reasignamos el contenido a un usuario (usa un ID válido de usuario)
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudo eliminar el usuario en WordPress',
                'details' => $response->json()
            ], $response->status());
        }

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno en el servidor',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function updateUser(Request $request, $id)
{
    // Validar los datos recibidos
    $request->validate([
        'name' => 'nullable|string',
        'email' => 'nullable|email',
        'role' => 'nullable|string',
    ]);

    // Capturamos el token JWT enviado en el encabezado de la solicitud
    $token = $request->header('Authorization'); 

    if (!$token) {
        return response()->json(['error' => 'Token no proporcionado'], 401);
    }

    // Preparar los datos que vamos a enviar a WordPress
    $userData = $request->only('name', 'email', 'role'); 

    // Filtrar campos vacíos
    $userData = array_filter($userData, function($value) {
        return !empty($value);
    });

    try {
        // Realizamos la solicitud a la API de WordPress para actualizar el usuario
        $response = Http::withHeaders([
            'Authorization' => $token, // Enviar el token JWT de autenticación
            'Content-Type' => 'application/json',
        ])->put(config('services.wordpress.api_url') . '/wp/v2/users/' . $id, $userData);

        // Si la respuesta no es exitosa, retornar el error
        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudo actualizar el usuario en WordPress',
                'details' => $response->json()
            ], $response->status());
        }

        // Si todo está bien, retornamos el mensaje de éxito
        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $response->json()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno en el servidor',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function listUsers(Request $request)
{
    // Capturamos el token JWT enviado en el encabezado de la solicitud
    $token = $request->header('Authorization');

    if (!$token) {
        return response()->json(['error' => 'Token no proporcionado'], 401);
    }

    try {
        // Realizamos la solicitud GET a la API de WordPress para obtener los usuarios
        $response = Http::withHeaders([
            'Authorization' => $token, // Enviamos el token JWT de autenticación
            'Content-Type' => 'application/json',
        ])->get(config('services.wordpress.api_url') . '/wp/v2/users');

        // Si la respuesta no es exitosa, retornamos el error
        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudieron obtener los usuarios de WordPress',
                'details' => $response->json()
            ], $response->status());
        }

        // Retornamos la lista de usuarios
        return response()->json([
            'users' => $response->json()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno en el servidor',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function getUserById(Request $request, $id)
{
    $token = $request->header('Authorization');

    if (!$token) {
        return response()->json(['error' => 'Token no proporcionado'], 401);
    }

    try {
        // Hacer la petición a WordPress
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->get(config('services.wordpress.api_url') . "/wp/v2/users/{$id}");

        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudo obtener el usuario de WordPress',
                'details' => $response->json()
            ], $response->status());
        }

        return response()->json($response->json());
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno en el servidor',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function login(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    try {
        // Hacemos la petición a la API de WordPress para obtener el token
        $response = Http::post(config('services.wordpress.api_url') . '/jwt-auth/v1/token', [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ]);

        // Verificamos si la respuesta es exitosa
        if (!$response->successful()) {
            return response()->json([
                'error' => 'No se pudo autenticar con WordPress',
                'details' => $response->json(),
            ], $response->status());
        }

        // Devolvemos el token de WordPress al usuario
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $response->json(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno en el servidor',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function logout(Request $request)
{
    // Eliminar el token del cliente
    return response()->json([
        'message' => 'Sesión cerrada correctamente. Elimina el token del lado del cliente.'
    ]);
}

    
}