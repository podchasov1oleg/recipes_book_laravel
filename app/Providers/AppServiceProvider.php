<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['recipes.create', 'recipes.edit'], function ($view) {
            $errors = View::shared('errors');

            // ошибки полей кол-ва продуктов
            $quantityErrors = collect($errors->get('products.*.quantity'))
                ->mapWithKeys(function ($messages, $key) {
                    preg_match('/products\.(\d+)\.quantity/', $key, $matches);

                    return [(int) $matches[1] => $messages[0]];
                });

            // остальные ошибки (title и products.*.quantity уже выводятся адресно)
            $otherErrors = collect($errors->getMessages())
                ->except('title')
                ->reject(fn ($messages, $key) => Str::is('products.*.quantity', $key))
                ->flatten();

            $view->with(compact('quantityErrors', 'otherErrors'));
        });
    }
}
