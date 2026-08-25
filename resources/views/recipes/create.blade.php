<x-layouts.app>
    <x-slot:title>
        Добавить рецепт
    </x-slot:title>

    <h1 class="h1">Добавить рецепт</h1>

    <form
        action="{{route('recipes.store')}}"
        method="POST"
        x-data="recipeForm(@js(json_decode(old('products', '[]'), true)), @js($quantityErrors))"
    >
        @csrf
        <input type="hidden" name="products" :value="JSON.stringify(products)">
        <label class="mb-2 block text-gray-700" for="title">Название рецепта:</label>
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

        <label class="my-2 block text-gray-700" for="product-ids">Продукты:</label>

        <table x-show="products.length > 0" class="table-fixed w-full border-separate border-spacing-0 mb-3">
            <colgroup>
                <col>
                <col class="w-37">
                <col class="w-12">
            </colgroup>
            <tbody>
                <template x-for="(product, index) in products" :key="product.product_id">
                    <tr>
                        <td
                            class="pl-4 pr-2 py-3 border-t border-l border-gray-300 bg-white capitalize"
                            :class="{
                                'rounded-tl-lg': index === 0,
                                'rounded-bl-lg': index === products.length - 1,
                                'border-b': index === products.length - 1,
                            }"
                            x-text="product.title"
                        ></td>
                        <td
                            class="px-2 py-3 border-t border-gray-300 bg-white"
                            :class="{
                                'border-b': index === products.length - 1,
                            }"
                        >
                            <div class="flex items-center gap-3">
                                <input class="text-input bg-gray-50 w-24" type="text" x-model.number="product.quantity">
                                <span class="inline-block text-gray-500 w-6" x-text="product.unit"></span>
                            </div>
                            <p
                                x-show="quantityErrors[index]"
                                x-text="quantityErrors[index]"
                                class="text-red-500 text-xs mt-1"
                            ></p>
                        </td>
                        <td
                            class="pr-4 pl-2 py-3 border-t border-r border-gray-300 bg-white text-right"
                            :class="{
                                'rounded-tr-lg': index === 0,
                                'rounded-br-lg': index === products.length - 1,
                                'border-b': index === products.length - 1,
                            }"
                        >
                            <button
                                x-on:click="removeProduct(index)"
                                class="btn-remove w-6 h-6 hover:bg-gray-100"
                                type="button"
                                title="Удалить продукт"
                            >&times;</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <select
            id="product-ids"
            x-ref="productSelect"
        >
            <option value="">Выберите продукт</option>
            @foreach($products as $product)
                <option
                    value="{{$product->id}}"
                    data-unit="{{$product->unit->value}}"
                    data-title="{{$product->title}}"
                >{{$product->title}}</option>
            @endforeach
        </select>

        @if($otherErrors->isNotEmpty())
            <div class="alert-error my-3">
                <ul>
                    @foreach($otherErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <button class="inline-block btn-primary cursor-pointer" type="submit">Создать</button>
            <a class="inline-block btn-secondary" href="{{route('recipes.index')}}">Назад к списку</a>
        </div>
    </form>

    @push('scripts')
        @vite('resources/ts/recipes/create.ts')
    @endpush
</x-layouts.app>
