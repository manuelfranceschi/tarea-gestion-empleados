<?php

use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

 Route::middleware('check-permiso')->group(function(){
    Route::prefix('user')->group(function(){
        Route::put('registrar', [userController::class, 'registrar']);
        Route::put('login', [userController::class, 'login'])->withoutMiddleware('check-permiso'); //sigue la funcion saltando el middleware
        Route::put('recuperarPassword', [userController::class, 'recuperarPassword'])->withoutMiddleware('check-permiso');
        Route::put('lista', [userController::class, 'lista']);
        Route::put('detalle', [userController::class, 'detalle']);
        Route::put('perfil', [userController::class, 'perfil'])->withoutMiddleware('check-permiso');
        Route::put('modificar', [userController::class, 'modificar']);

    });
 });


