<x-layouts.app>
    <x-slot:title>
        Добавить рецепт
    </x-slot:title>

    <h1 class="h1">Добавить рецепт</h1>

    <form action="{{route('recipes.store')}}" method="POST">
        @csrf
        <label class="mb-2 block" for="title">Название рецепта:</label>
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

        <label class="my-2 block" for="product-ids">Продукты рецепта:</label>

        <select
            @class([
                'is-invalid' => $errors->has('product_ids'),
            ])
            name="product_ids[]"
            id="product-ids"
            multiple
        >
            @foreach($products as $product)
                <option
                    value="{{$product->id}}"
                    @selected(in_array($product->id, old('product_ids', [])))
                >{{$product->title}}</option>
            @endforeach
        </select>

        @error('product_ids')
            <p class="text-red-500 text-sm">{{$message}}</p>
        @enderror

        <div>
            <button class="inline-block btn-primary cursor-pointer" type="submit">Создать</button>
            <a class="inline-block btn-secondary" href="{{route('recipes.index')}}">Назад к списку</a>
        </div>
    </form>

    @push('scripts')
        @vite('resources/js/recipes/create.js')
    @endpush
</x-layouts.app>
