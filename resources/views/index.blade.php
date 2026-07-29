<x-layouts.app>
    <x-slot:title>
        Главная страница
    </x-slot>

    <h1 class="h1">Планируй неделю - покупай по списку</h1>
    <p>Выбери рецепты на неделю, а сервис сам посчитает, сколько и каких продуктов взять в магазине.</p>
    <a href="#" class="inline-block btn-primary">Смотреть рецепты</a>
    <a href="{{ route('products.index') }}" class="inline-block btn-secondary">Список продуктов</a>
</x-layouts.app>
