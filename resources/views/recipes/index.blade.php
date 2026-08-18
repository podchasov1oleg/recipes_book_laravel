<x-layouts.app>
    <x-slot:title>
        Рецепты
    </x-slot:title>

    <div class="flex justify-between items-center">
        <h1 class="h1">Рецепты</h1>
        <a
            href="{{ route('recipes.create') }}"
            class="btn-primary"
        >+ Добавить рецепт</a>
    </div>

    @if(session('success'))
        <p class="alert-success mb-2">{{ session('success') }}</p>
    @endif

    @if($recipes->count())
        <div class="grid grid-cols-2 gap-2">
            @foreach($recipes as $recipe)
                <div class="border border-gray-200 rounded-lg flex flex-col p-3 bg-white">
                    <p class="text-lg font-bold text-black mb-2">{{$recipe->title}}</p>
                    <p class="text-slate-400 text-sm mb-2">
                        {{trans_choice('recipes.products_count', $recipe->products_count)}}
                    </p>
                    <div>
                        <a
                            class="inline-block text-blue-500 text-sm mr-1"
                            href="{{route('recipes.edit', $recipe)}}"
                        >Изменить</a>
                        <form
                            action="{{route('recipes.destroy', $recipe)}}"
                            method="POST"
                            class="inline-block"
                        >
                            @method('DELETE')
                            @csrf
                            <button class="inline-block text-red-500 text-sm cursor-pointer mr-1" type="submit">
                                Удалить
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-lg my-6">Ни одного рецепта не создано!</p>
    @endif
</x-layouts.app>
