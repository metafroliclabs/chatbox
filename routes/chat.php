<?php

use Illuminate\Support\Facades\Route;
use Metafroliclabs\LaravelChat\Controllers\ChatController;
use Metafroliclabs\LaravelChat\Controllers\ChatMessageController;
use Metafroliclabs\LaravelChat\Controllers\ChatUserController;

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
    // Chat list endpoints
    Route::get('/all/list', [ChatController::class, 'index']);
    Route::get('/unread/list', [ChatController::class, 'unread_list']);
    Route::get('/unread/count', [ChatController::class, 'unread_count']);

    // Chat creation (with throttling)
    Route::middleware('throttle:chats')->group(function () {
        Route::post('/create', [ChatController::class, 'create']);
        Route::post('/create/group', [ChatController::class, 'create_group']);
    });

    // Chat actions
    Route::post('/{id}/update', [ChatController::class, 'update']);
    Route::post('/{id}/delete', [ChatController::class, 'delete']);
    Route::post('/{id}/leave', [ChatController::class, 'leave']);
    Route::post('/{id}/mute', [ChatController::class, 'mute']);
    Route::get('/{id}', [ChatController::class, 'show']);

    // Users
    Route::get('/{id}/users', [ChatUserController::class, 'get_users']);
    Route::post('/{id}/users/add', [ChatUserController::class, 'add_users']);
    Route::post('/{id}/users/remove', [ChatUserController::class, 'remove_users']);
    Route::post('/{id}/users/{uid}/admin', [ChatUserController::class, 'manage_admin']);

    // Messages (with message throttling)
    Route::middleware('throttle:messages')->group(function () {
        Route::post('/{id}/messages', [ChatMessageController::class, 'send_message']);
        Route::post('/{id}/messages/forward', [ChatMessageController::class, 'forward_messages']);
    });
    Route::get('/{id}/messages', [ChatMessageController::class, 'index']);
    Route::get('/{id}/messages/{mid}/likes', [ChatMessageController::class, 'get_message_likes']);
    Route::post('/{id}/messages/{mid}/likes', [ChatMessageController::class, 'like_message']);
    Route::get('/{id}/messages/{mid}/views', [ChatMessageController::class, 'get_message_views']);
    Route::post('/{id}/messages/{mid}/views', [ChatMessageController::class, 'view_message']);
    Route::post('/{id}/messages/{mid}/update', [ChatMessageController::class, 'update_message']);
    Route::post('/{id}/messages/{mid}/delete', [ChatMessageController::class, 'delete_message']);
});
