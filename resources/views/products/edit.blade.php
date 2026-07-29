<x-layouts.app>
    <x-slot:title>
        Изменить продукт
    </x-slot>

    <h1 class="h1">Изменить продукт</h1>

    <form class="" action="{{route('products.update', $product)}}" method="POST">
        @csrf
        @method('PUT')
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
                value="{{old('title', $product->title)}}"
                placeholder="Введите название"
            />
            @error('title')
                <p class="text-red-500 text-sm">{{$message}}</p>
            @enderror

            <div>
                <button class="inline-block btn-primary cursor-pointer" type="submit">Сохранить</button>
                <a class="inline-block btn-secondary" href="{{route('products.index')}}">Назад к списку</a>
            </div>
        </div>
    </form>
</x-layouts.app>
