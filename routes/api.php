<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ProductoController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('productos', ProductoController::class);

Route::get('/interno/productos/{producto}', function (Producto $producto) {
    $claveConfigurada = config('services.grpc.internal_key');
    $claveRecibida = request()->header('X-Internal-Key');

    if (empty($claveConfigurada) || ! hash_equals($claveConfigurada, (string) $claveRecibida)) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    return response()->json($producto);
});
