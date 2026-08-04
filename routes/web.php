<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

// главная страница
Route::get('/', fn () => view('index'))->name('home');
// страница продуктов
Route::resource('products', ProductController::class)->except('show');
// страницы рецептов
Route::resource('recipes', RecipeController::class)->except('show');
