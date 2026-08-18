<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\WeekMenuController;
use Illuminate\Support\Facades\Route;

// главная страница
Route::get('/', fn () => view('index'))->name('home');
// страница продуктов
Route::resource('products', ProductController::class)->except('show');
// страницы рецептов
Route::resource('recipes', RecipeController::class)->except('show');
// страница меню на неделю
Route::get('/week-menu', [WeekMenuController::class, 'index'])->name('week-menu');
// сохранение рецептов на день
Route::post('/week-menu', [WeekMenuController::class, 'store'])->name('week-menu.store');
// удалить рецепт для дня
Route::delete('/week-menu/{menuDay}/{recipe}', [WeekMenuController::class, 'destroy'])
    ->name('week-menu.destroy');
