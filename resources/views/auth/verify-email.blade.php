<x-layouts.app>
    <x-slot:title>Подтвердите почту</x-slot:title>

    <div class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-md p-8">
        <h1 class="mb-4 text-2xl font-bold">Подтвердите почту</h1>
        <p class="mb-3 text-gray-700 text-base/normal">
            Мы отправили письмо со ссылкой на <b class="font-bold">{{auth()->user()->email}}</b>. Откройте ссылку из
            письма, чтобы получить доступ к продуктам, рецептам и меню.
        </p>
        <p class="mb-6 text-sm text-gray-500">Не пришло? Проверьте папку «Спам» или отправьте письмо заново.</p>

        @if(session('message'))
            <p class="alert-success my-6">{{ session('message') }}</p>
        @endif

        <form class="inline-block" action="{{route('verification.send')}}" method="POST">
            @csrf
            <button type="submit" class="btn-primary my-0">Отправить письмо еще раз</button>
        </form>
        <form class="inline-block" action="{{route('logout')}}" method="POST">
            @csrf
            <button type="submit" class="btn-outline-secondary my-0">Выйти</button>
        </form>
    </div>
</x-layouts.app>
