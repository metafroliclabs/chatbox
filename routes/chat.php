<?php

use Illuminate\Support\Facades\Route;
use Metafroliclabs\LaravelChat\Http\Controllers\ChatController;

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

Route::controller(ChatController::class)->group(function () {
    Route::post('/chat/create', 'create_chat');
    Route::get('/chat/unread/count', 'get_unread_count');
    Route::get('/chat/unread/list', 'get_unread_chats');
    Route::get('/chat/all/list', 'get_chat_list');
    Route::get('/chat/{id}', 'get_chat');
    Route::post('/chat/{id}/send', 'send_message');
});
