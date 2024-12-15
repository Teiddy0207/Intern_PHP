<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
    return view('login'); 
});

Route::get('/email/{email}', [AuthController::class, 'showEmailPage'])->name('email.page');
