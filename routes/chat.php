<?php

use Illuminate\Support\Facades\Route;
use Metafroliclabs\LaravelChat\Controllers\ChatController;

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

    Route::controller(ChatController::class)->group(function () {
        Route::post('/create', 'create_chat');
        Route::get('/unread/count', 'get_unread_count');
        Route::get('/unread/list', 'get_unread_chats');
        Route::get('/all/list', 'get_chat_list');
        Route::get('/{id}', 'get_chat');
        Route::post('/{id}/send', 'send_message');
    });
    
});
