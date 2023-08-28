<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/cache_tag', [\App\Http\Controllers\CacheController::class, 'create']);
Route::delete('/cache_tag', [\App\Http\Controllers\CacheController::class, 'delete']);
Route::get('/cache_tag/check', [\App\Http\Controllers\CacheController::class, 'check']);
