<?php

use Illuminate\Support\Facades\Route;

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