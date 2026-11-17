<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationController;
use App\Http\Middleware\AuthenticateSiteToken;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticateSiteToken::class)->group(function () {
    Route::post('chat', [ChatController::class, 'store'])->middleware('throttle:30,1');
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
});
