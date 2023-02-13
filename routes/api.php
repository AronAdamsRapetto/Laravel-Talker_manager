<?php

use App\Http\Controllers\TalkerController;
use App\Http\Controllers\LoginController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(TalkerController::class)->group(function () {
    Route::get('/talkers', 'getAllTalkers');
    Route::get('/talker/{id}', 'getTalker');
    Route::post('/talker', 'createTalker')->middleware('TalkerVerifyFields');
    Route::put('/talker/{id}', 'updateTalker')->middleware('TalkerVerifyFields');
    Route::delete('/talker/{id}', 'deleteTalker');
    Route::get('/talkers/search', 'searchTalker');
});

Route::post('/login', [LoginController::class, 'login'])->middleware('LoginVerifyFields');