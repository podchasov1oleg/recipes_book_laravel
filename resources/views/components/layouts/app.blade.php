<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body>
        <header class="shadow-md">
            <nav class="container mx-auto flex justify-center py-3">
                <ul class="flex items-center list-none pl-0 mb-0">
                    <li>
                        <a
                            href="/"
                            @class([
                                'btn-primary' => request()->routeIs('home'),
                                'menu-link' => !request()->routeIs('home'),
                            ])
                        >Главная</a>
                    </li>
                    <li>
                        <a
                            href="{{ route('products.index') }}"
                            @class([
                                'btn-primary' => request()->routeIs('products.*'),
                                'menu-link' => !request()->routeIs('products.*'),
                            ])
                        >Продукты</a>
                    </li>
                    <li>
                        <a href="#" class="menu-link">Рецепты</a>
                    </li>
                    <li>
                        <a href="#" class="menu-link">Меню на неделю</a>
                    </li>
                </ul>
            </nav>
        </header>

        <main class="container mx-auto max-w-2xl px-4 py-8">
            {{ $slot }}
        </main>

        @stack('scripts')
    </body>
</html>
