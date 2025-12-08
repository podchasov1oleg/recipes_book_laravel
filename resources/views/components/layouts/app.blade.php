<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body>
        <header class="shadow-md">
            <nav class="container mx-auto flex justify-center py-3">
                <ul class="flex flex-wrap list-none pl-0 mb-0">
                    <li>
                        <a href="#" class="block py-2 px-3 text-base text-white decoration-0 bg-blue-500 rounded-md">Главная</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-base decoration-0 text-blue-600 hover:text-blue-500 transition-colors">Продукты</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-base decoration-0 text-blue-600 hover:text-blue-500 transition-colors">Рецепты</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-base decoration-0 text-blue-600 hover:text-blue-500 transition-colors">Меню на неделю</a>
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
