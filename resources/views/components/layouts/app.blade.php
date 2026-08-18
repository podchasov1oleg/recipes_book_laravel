@props([
    'maxWidth' => 'max-w-2xl',
    'bgColor' => null,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="bg-gray-100">
        <header class="shadow-md bg-white">
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
                        <a
                            href="{{route('recipes.index')}}"
                            @class([
                                'btn-primary' => request()->routeIs('recipes.*'),
                                'menu-link' => !request()->routeIs('recipes.*'),
                            ])
                        >Рецепты</a>
                    </li>
                    <li>
                        <a
                            href="{{route('week-menu')}}"
                            @class([
                                'btn-primary' => request()->routeIs('week-menu'),
                                'menu-link' => !request()->routeIs('week-menu'),
                            ])
                        >Меню на неделю</a>
                    </li>
                </ul>
            </nav>
        </header>

        <main
            {{$attributes->class([
                'container',
                'mx-auto',
                'px-4',
                'py-8',
                $maxWidth,
                $bgColor,
            ])}}
        >
            {{ $slot }}
        </main>

        @stack('scripts')
    </body>
</html>
