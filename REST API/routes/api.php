<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FollowingController;
use App\Http\Controllers\Api\FollowerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('v1/auth/register', [AuthController::class, 'register']);

Route::post('v1/auth/login', [AuthController::class, 'login']);

Route::post('v1/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

Route::post('v1/posts', [PostController::class, 'create_post'])->middleware(['auth:sanctum']);

Route::delete('v1/posts/{id}', [PostController::class, 'delete_post'])->middleware(['auth:sanctum']);

Route::get('v1/posts', [PostController::class, 'get_allpost'])->middleware(['auth:sanctum']);

Route::post('v1/users/{username}/follow', [FollowingController::class, 'follow_user'])->middleware(['auth:sanctum']);

Route::delete('v1/users/{username}/unfollow', [FollowingController::class, 'unfollow_user'])->middleware(['auth:sanctum']);

Route::get('v1/following', [FollowingController::class, 'user_following'])->middleware(['auth:sanctum']);

Route::put('v1/users/{username}/accept', [FollowerController::class, 'accept_follow'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}/followers', [FollowerController::class, 'user_follower'])->middleware(['auth:sanctum']);

Route::get('v1/users', [UserController::class, 'get_user'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}', [UserController::class, 'detail_user'])->middleware(['auth:sanctum']);
