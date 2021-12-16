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
        Route::put('login', [userController::class, 'login']);
        Route::put('recuperarPassword', [userController::class, 'recuperarPassword']);
        Route::put('lista', [userController::class, 'lista']);
        Route::put('detalle', [userController::class, 'detalle']);
        Route::put('perfil', [userController::class, 'perfil']);
        Route::put('modificar', [userController::class, 'modificar']);

    });
 });


