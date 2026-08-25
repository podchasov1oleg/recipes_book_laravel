<x-layouts.app>
    <x-slot:title>
        Добавить продукт
    </x-slot>

    <h1 class="h1">Добавить продукт</h1>

    <form action="{{route('products.store')}}" method="POST">
        @csrf
        <div>
            <label class="mb-1 block text-gray-700" for="title">Название продукта:</label>
            <input
                @class([
                    'block',
                    'text-input',
                    'mb-3' => !$errors->has('title'),
                    'is-invalid' => $errors->has('title'),
                ])
                id="title"
                type="text"
                name="title"
                value="{{old('title')}}"
                placeholder="Введите название"
            />
            @error('title')
                <p class="text-red-500 text-sm mb-3">{{$message}}</p>
            @enderror

            <label class="mb-1 block text-gray-700" for="unit">Единица измерения:</label>
            <select
                @class([
                    'select-input',
                    'mb-3' => !$errors->has('unit'),
                    'is-invalid' => $errors->has('unit'),
                ])
                name="unit"
                id="unit"
            >
                @foreach($units as $value => $label)
                    <option
                        value="{{$value}}"
                        @selected(old('unit') == $value)
                    >{{$label}} ({{$value}})</option>
                @endforeach
            </select>
            @error('unit')
                <p class="text-red-500 text-sm mb-3">{{$message}}</p>
            @enderror

            <div>
                <button class="inline-block btn-primary cursor-pointer" type="submit">Создать</button>
                <a class="inline-block btn-secondary" href="{{route('products.index')}}">Назад к списку</a>
            </div>
        </div>
    </form>
</x-layouts.app>
