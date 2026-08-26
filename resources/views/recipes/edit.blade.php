<x-layouts.app>
    <x-slot:title>
        Изменить рецепт
    </x-slot:title>

    <h1 class="h1">Изменить рецепт</h1>

    @php
        $initialProducts = old('products')
            ? json_decode(old('products'), true)
            : $recipe->products->map(fn ($product) => [
                'product_id' => $product->id,
                'unit' => $product->unit->value,
                'quantity' => $product->pivot->quantity,
                'title' => $product->title,
            ])->all();
    @endphp

    <form
        action="{{route('recipes.update', $recipe)}}"
        method="POST"
        x-data="recipeForm(@js($initialProducts), @js($quantityErrors))"
    >
        @csrf
        @method('PUT')

        <input type="hidden" name="products" :value="JSON.stringify(products)">

        <div class="flex gap-3">
            <div class="w-full">
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
                    value="{{old('title', $recipe->title)}}"
                    placeholder="Введите название"
                />
                @error('title')
                    <p class="text-red-500 text-sm">{{$message}}</p>
                @enderror
            </div>
            <div class="w-40">
                <label for="servings" class="mb-2 block text-gray-700">Порций:</label>
                <input
                    @class([
                        'block',
                        'text-input',
                        'is-invalid' => $errors->has('servings'),
                    ])
                    type="number"
                    min="1"
                    max="255"
                    id="servings"
                    name="servings"
                    value="{{old('servings', $recipe->servings)}}"
                />
                @error('servings')
                    <p class="text-red-500 text-sm">{{$message}}</p>
                @enderror
            </div>
        </div>

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
            <button class="inline-block btn-primary cursor-pointer" type="submit">Сохранить</button>
            <a class="inline-block btn-secondary" href="{{route('recipes.index')}}">Назад к списку</a>
        </div>
    </form>

    @push('scripts')
        @vite('resources/ts/recipes/create.ts')
    @endpush
</x-layouts.app>
