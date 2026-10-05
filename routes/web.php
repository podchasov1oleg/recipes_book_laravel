<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LoginUserController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\WeekMenuController;
use Illuminate\Support\Facades\Route;

// главная страница
Route::get('/', fn () => view('index'))->name('home');

// доступно гостям
Route::middleware('guest')->group(function () {
    // форма регистрации
    Route::get('/register', [RegisterUserController::class, 'index'])->name('register');
    // регистрация пользователя
    Route::post('/register', [RegisterUserController::class, 'store'])->name('register.store');
    // форма авторизации
    Route::get('/login', [LoginUserController::class, 'index'])->name('login');
    // авторизовать пользователя
    Route::post('/login', [LoginUserController::class, 'auth'])
        ->middleware(['throttle:6,1'])
        ->name('login.auth');

    // форма забытого пароля
    Route::get('/forgot-password', [PasswordResetController::class, 'index'])->name('password.request');
    // обработчик формы восстановления пароля
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    // форма создания нового пароля
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    // обработчик формы нового пароля
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

// доступно вошедшим
Route::middleware('auth')->group(function () {
    // деавторизовать пользователя
    Route::post('/logout', [LoginUserController::class, 'logout'])->name('logout');

    // Подтверждение почты
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

// доступно вошедшему и подтвердившему почту
Route::middleware(['auth', 'verified'])->group(function () {
    /// Продукты
    // страница продуктов
    Route::resource('products', ProductController::class)->except('show');

    /// Рецепты
    // страницы рецептов
    Route::resource('recipes', RecipeController::class)->except('show');

    /// Меню на неделю
    // страница меню на неделю
    Route::get('/week-menu', [WeekMenuController::class, 'index'])->name('week-menu');
    // сохранение рецептов на день
    Route::post('/week-menu', [WeekMenuController::class, 'store'])->name('week-menu.store');
    // изменение кол-ва порций для рецепта на день
    Route::patch('/week-menu/{menuDay}/{recipe}', [WeekMenuController::class, 'updateServings'])
        ->name('week-menu.update-servings');
    // удалить рецепт для дня
    Route::delete('/week-menu/{menuDay}/{recipe}', [WeekMenuController::class, 'destroy'])
        ->name('week-menu.destroy');
    // получить список продуктов на неделю
    Route::get('/week-menu/shopping-list', [WeekMenuController::class, 'shoppingList'])
        ->name('week-menu.shopping-list');
});
