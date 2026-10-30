<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudyResourceController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1'); // 6 attempts per minute

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // {resource} is bound to a StudyResource by its id (route model binding); unknown ids get 404.
    Route::apiResource('resources', StudyResourceController::class);
    Route::get('/resources/{resource}/download', [StudyResourceController::class, 'download'])
        ->name('resources.download');
});
