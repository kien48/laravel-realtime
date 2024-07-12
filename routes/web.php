<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::middleware('check.user')->group(function (){
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'chat'])->name('chat');
    Route::post('/send', [App\Http\Controllers\ChatController::class, 'send'])->name('send');
    Route::get('/chat-rieng/{id}', [App\Http\Controllers\ChatController::class, 'chatPrivate'])->name('chatPrivate');
    Route::post('/send-private/{iduser}', [App\Http\Controllers\ChatController::class, 'sendPrivate'])->name('sendPrivate');
    Route::put('/leaving/{id}', [App\Http\Controllers\ChatController::class, 'leaving'])->name('leaving');
});
