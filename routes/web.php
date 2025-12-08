<?php

use Illuminate\Support\Facades\Route;

// главная страница
Route::get('/', fn () => view('index'));
