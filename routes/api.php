<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordpressApiController;
use App\Http\Controllers\WordPressAuthController;


Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando perfectamente!',
        'status' => 'success'
    ]);
});

Route::get('/productos', function () {
    return response()->json([
        'message' => 'todos los productos',
        'status' => 'success'
    ]);

});

Route::get('/usuarios', function () {
    return response()->json([
        'message' => 'todos los usuarios',
        'status' => 'success'
    ]);

});

Route::get('/youtube', function () {
    return response()->json([
        'message' => 'todos los usuarios',
        'status' => 'success'
    ]);

});


Route::get('/wp/users', [WordPressAuthController::class, 'listUsers']);
Route::post('/wp/users', [WordPressAuthController::class, 'createUser']);
Route::put('/wp/users/{user_id}', [WordPressAuthController::class, 'updateUser']);
Route::delete('/wp/users/{user_id}', [WordPressAuthController::class, 'deleteUser']);
Route::post('/wp/token', [WordPressAuthController::class, 'getToken']);
Route::get('/wp/users/{id}', [WordPressAuthController::class, 'getUserById']);

