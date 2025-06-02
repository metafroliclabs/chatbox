<?php

use Illuminate\Support\Facades\Route;
use Metafroliclabs\LaravelChat\Controllers\ChatController;
use Metafroliclabs\LaravelChat\Controllers\ChatMessageController;

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


Route::middleware(config('chat.middleware'))->prefix(config('chat.prefix'))->group(function () {
    Route::get('/all/list', [ChatController::class, 'index']);
    Route::get('/unread/list', [ChatController::class, 'unread_list']);
    Route::get('/unread/count', [ChatController::class, 'unread_count']);

    Route::post('/create', [ChatController::class, 'create']);
    Route::post('/create/group', [ChatController::class, 'create_group']);
    // Route::post('/{id}/update', [ChatController::class, 'update']);
    Route::post('/{id}/delete', [ChatController::class, 'delete']);
    Route::post('/{id}/leave', [ChatController::class, 'leave']);

    Route::get('/{id}/users', [ChatController::class, 'get_users']);
    Route::post('/{id}/users/add', [ChatController::class, 'add_users']);
    Route::post('/{id}/users/remove', [ChatController::class, 'remove_users']);
    // Route::post('/{id}/users/{uid}/admin', [ChatController::class, 'manage_admin']);

    Route::get('/{id}/messages', [ChatMessageController::class, 'index']);
    Route::post('/{id}/messages', [ChatMessageController::class, 'send_message']);
    Route::get('/{id}/messages/{mid}/likes', [ChatMessageController::class, 'get_likes']);
    Route::post('/{id}/messages/{mid}/like', [ChatMessageController::class, 'like_message']);
    // Route::get('/{id}/messages/{mid}/views', [ChatMessageController::class, 'get_views']);
    // Route::post('/{id}/messages/{mid}/update', [ChatMessageController::class, 'update_message']);
    // Route::post('/{id}/messages/{mid}/delete', [ChatMessageController::class, 'delete_message']);
});
