<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\StudyResourceController;
use App\Http\Controllers\VoteController;
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

    // One vote per user per resource, so "vote" is singular and has no id of its own.
    Route::put('/resources/{resource}/vote', [VoteController::class, 'update'])->name('resources.vote.update');
    Route::delete('/resources/{resource}/vote', [VoteController::class, 'destroy'])->name('resources.vote.destroy');

    // shallow(): list/create are nested under the resource; edit/delete use /comments/{comment}.
    Route::apiResource('resources.comments', CommentController::class)->shallow()->except('show');
});
