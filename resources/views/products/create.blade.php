<x-layouts.app>
    <x-slot:title>
        Добавить продукт
    </x-slot>

    <h1 class="h1">Добавить продукт</h1>

    <form class="" action="{{route('products.store')}}" method="POST">
        @csrf
        <div>
            <label class="mb-2 block" for="title">Название продукта:</label>
            <input
                @class([
                    'block',
                    'text-input',
                    'is-invalid' => $errors->has('title'),
                ])
                id="title"
                type="text"
                name="title"
                value="{{old('title')}}"
                placeholder="Введите название"
            />
            @error('title')
                <p class="text-red-500 text-sm">{{$message}}</p>
            @enderror

            <div>
                <button class="inline-block btn-primary cursor-pointer" type="submit">Создать</button>
                <a class="inline-block btn-secondary" href="{{route('products.index')}}">Назад к списку</a>
            </div>
        </div>
    </form>
</x-layouts.app>
