<header class="p-3 border-b border-b-gray-300">
    <div class="flex justify-between">
        <h3 class="text-lg font-bold">Список продуктов</h3>
        <button class="js-shopping-list-close w-6 h-6 grid place-items-center cursor-pointer leading-none border border-gray-300 text-gray-400 transition-colors bg-white hover:text-red-400 rounded-sm" type="button">&times;</button>
    </div>

    <span class="text-xs text-gray-500">{{trans_choice('recipes.products_count', $result->count())}}</span>
</header>
<div class="p-3 overflow-y-auto">
    <ul>
        @foreach($result as $item)
            <li class="p-2 rounded-md hover:bg-gray-100 flex justify-between">
                <span>{{$item['title']}}</span>
                <span class="text-gray-500">{{$item['quantity']}} {{$item['unit']}}</span>
            </li>
        @endforeach
    </ul>
</div>
