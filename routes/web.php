<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// главная страница
Route::get('/', fn () => view('index'))->name('home');
// страница продуктов
Route::resource('products', ProductController::class)->except('show');
