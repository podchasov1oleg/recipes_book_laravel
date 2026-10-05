@props([
    'maxWidth' => 'max-w-3xl',
    'bgColor' => null,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/ts/app.ts'])
        @stack('styles')
    </head>
    <body class="bg-gray-100">
        <header class="shadow-md bg-white">
            <nav class="container mx-auto flex justify-between py-3 max-w-3xl">
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
                                'pointer-events-none text-gray-400' => !auth()->check(),
                            ])
                        >Продукты</a>
                    </li>
                    <li>
                        <a
                            href="{{route('recipes.index')}}"
                            @class([
                                'btn-primary' => request()->routeIs('recipes.*'),
                                'menu-link' => !request()->routeIs('recipes.*'),
                                'pointer-events-none text-gray-400' => !auth()->check(),
                            ])
                        >Рецепты</a>
                    </li>
                    <li>
                        <a
                            href="{{route('week-menu')}}"
                            @class([
                                'btn-primary' => request()->routeIs('week-menu'),
                                'menu-link' => !request()->routeIs('week-menu'),
                                'pointer-events-none text-gray-400' => !auth()->check(),
                            ])
                        >Меню на неделю</a>
                    </li>
                </ul>

                {{--неавторизованный пользователь--}}
                @guest
                    <ul class="flex items-center list-none pl-0 mb-0 gap-1">
                        <li>
                            <a
                                href="{{route('login')}}"
                                @class([
                                    'btn-primary' => request()->routeIs('login'),
                                    'menu-link' => !request()->routeIs('login'),
                                ])
                            >Войти</a>
                        </li>
                        <li>
                            <a
                                href="{{route('register')}}"
                                @class([
                                    'btn-primary' => request()->routeIs('register'),
                                    'menu-link' => !request()->routeIs('register'),
                                ])
                            >Регистрация</a>
                        </li>
                    </ul>
                @endguest

                {{--авторизованный пользователь--}}
                @auth
                    <ul class="flex items-center list-none pl-0 mb-0 gap-2">
                        @if(!auth()->user()->hasVerifiedEmail())
                            <li>
                                <a
                                    href="{{route('verification.notice')}}"
                                    class="decoration-0 text-blue-600 hover:text-blue-500 transition-colors duration-150 text-sm"
                                >Подтвердите почту</a>
                            </li>
                        @endif
                        <li>{{auth()->user()->name}}</li>
                        <li>
                            <form action="{{route('logout')}}" method="POST">
                                @csrf
                                <button
                                    type="submit"
                                    class="btn-secondary cursor-pointer my-0"
                                >Выйти</button>
                            </form>
                        </li>
                    </ul>
                @endauth
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
