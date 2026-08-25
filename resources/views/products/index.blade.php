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
                                'px-4',
                                'py-3',
                                'border-t',
                                'border-l',
                                'border-gray-300',
                                'bg-white',
                                'border-b' => $loop->last,
                                'rounded-bl-xl' => $loop->last,
                                'rounded-tl-xl' => $loop->first,
                            ])
                        >
                            <span class="capitalize">{{$product->title}}</span>
                            <span
                                class="text-sm text-gray-400 py-0.5 px-3 border border-gray-300 rounded-xl ml-2 bg-gray-50"
                            >{{$product->unit}}</span>
                        </td>
                        <td
                            @class([
                                'px-4',
                                'py-3',
                                'border-t',
                                'border-r',
                                'border-gray-300',
                                'bg-white',
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
