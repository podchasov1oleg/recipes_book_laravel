<x-layouts.app>
    <x-slot:title>
        Продукты
    </x-slot>

    <div class="flex justify-between items-center">
        <h1 class="h1">Продукты</h1>
        <a
            href="{{ route('products.create') }}"
            class="btn-primary"
        >+ Добавить продукт</a>
    </div>

    {{--flash message--}}
    @if(session('success'))
        <p class="alert-success mb-2">{{ session('success') }}</p>
    @endif

    {{--products table--}}
    @if($products->count())
        <table class="table-auto w-full border-separate border-spacing-0">
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td
                            @class([
                                'px-3',
                                'py-2',
                                'border-t',
                                'border-l',
                                'border-gray-400',
                                'border-b' => $loop->last,
                                'rounded-bl-xl' => $loop->last,
                                'rounded-tl-xl' => $loop->first,
                            ])
                        >{{$product->title}}</td>
                        <td
                            @class([
                                'px-3',
                                'py-2',
                                'border-t',
                                'border-r',
                                'border-gray-400',
                                'border-b' => $loop->last,
                                'rounded-br-xl' => $loop->last,
                                'rounded-tr-xl' => $loop->first,
                                'text-right',
                            ])
                        >
                            <a
                                class="inline-block text-blue-500 mx-1 text-sm"
                                href="{{route('products.edit', ['product' => $product->id])}}"
                            >Изменить</a>
                            <form
                                action="{{route('products.destroy', ['product' => $product->id])}}"
                                method="POST"
                                class="inline"
                            >
                                @method('DELETE')
                                @csrf
                                <button class="inline-block text-red-500 mx-1 text-sm cursor-pointer" type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-lg my-6">Ни одного товара не создано!</p>
    @endif
</x-layouts.app>
