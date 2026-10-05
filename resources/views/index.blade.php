<x-layouts.app>
    <x-slot:title>
        Главная страница
    </x-slot>

    <h1 class="h1">Планируй неделю - покупай по списку</h1>

    @if(session('message'))
        <p class="alert-success my-6">{{ session('message') }}</p>
    @endif

    <p class="text-lg text-gray-700 mb-3">Выбери рецепты на неделю, а сервис сам посчитает, сколько и каких продуктов взять в магазине.</p>
    <p class="text-gray-500 mb-3">
        Рецепты и меню хранятся в вашем домохозяйстве - оно создаётся вместе с аккаунтом.
    </p>
    <a href="{{route('register')}}" class="inline-block btn-primary mr-2">Начать - это бесплатно</a>
    <a href="{{ route('login') }}" class="inline-block btn-secondary">Войти</a>
</x-layouts.app>
