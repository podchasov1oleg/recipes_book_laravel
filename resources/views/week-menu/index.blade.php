@php use Carbon\Carbon; @endphp
<x-layouts.app max-width="max-w-4xl">
    <x-slot:title>
        Меню на неделю
    </x-slot:title>

    {{-- заголовок + кнопка списка покупок --}}
    <div class="flex justify-between items-center">
        <h1 class="h1">Меню на неделю</h1>
        <button
            class="js-shopping-list-btn btn-primary"
            data-monday="{{$monday->format('Y-m-d')}}"
        >Список покупок</button>
    </div>

    @if(session('success'))
        <p class="alert-success mb-2">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div class="alert-error mb-2">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{--переключатель недель--}}
    <div class="flex flex-row items-center mb-5">
        <a href="?monday={{$monday->copy()->subWeek()->toDateString()}}" class="btn-outline">&lsaquo;</a>
        <span class="font-bold px-2">
            {{$monday->translatedFormat('d F')}} - {{$sunday->translatedFormat('d F Y')}}
        </span>
        <a href="?monday={{$monday->copy()->addWeek()->toDateString()}}" class="btn-outline">&rsaquo;</a>
    </div>

    <ul class="grid grid-cols-7 gap-2">
        @foreach($menuDays as $stringDay => $menuDay)
            <li class="week-day">
                <header class="week-day-header">
                    <span
                        class="text-gray-500 font-bold text-sm uppercase"
                    >{{ Carbon::parse($stringDay)->isoFormat('dd') }}</span>
                    <time
                        class="text-gray-400 text-sm"
                        datetime="{{$stringDay}}"
                    >{{ Carbon::parse($stringDay)->translatedFormat('j F') }}</time>
                </header>
                <ul class="p-2 space-y-3 flex-1">
                    @forelse($menuDay->recipes as $recipe)
                        <li class="week-recipe">
                            <span class="font-semibold min-w-0 wrap-break-word">{{ $recipe->title }}</span>
                            <form
                                action="{{route('week-menu.destroy', ['menuDay' => $menuDay, 'recipe' => $recipe])}}"
                                method="POST"
                            >
                                @method('DELETE')
                                @csrf
                                <button class="btn-remove" type="submit" title="Удалить рецепт">&times;</button>
                            </form>
                            <span class="text-gray-500 text-xs col-span-2">
                                {{trans_choice('recipes.products_count', $recipe->products_count)}}
                            </span>
                        </li>
                    @empty
                        <li class="week-day-empty">
                            Пока ничего не запланировано
                        </li>
                    @endforelse
                </ul>
                <footer class="p-2 pt-0">
                    <form class="js-add-recipes-form" action="{{route('week-menu.store')}}" method="POST">
                        @csrf
                        <input type="hidden" name="day" value="{{$stringDay}}">
                        <button class="js-add-recipes-btn btn-add" type="button">+ Рецепт</button>

                        <div class="js-recipe-popup recipe-popup hidden">
                            <select multiple name="recipe_ids[]" class="recipes-select js-recipes-select">
                                @foreach($recipeDays[$stringDay] as $recipe)
                                    <option value="{{$recipe->id}}">{{$recipe->title}}</option>
                                @endforeach
                            </select>

                            <div class="mt-2 border-t border-t-gray-300 flex justify-between gap-1">
                                <button type="button" class="js-cancel-btn btn-secondary mb-0 w-full cursor-pointer">
                                    Отмена
                                </button>
                                <button type="submit" class="btn-primary mb-0 w-full cursor-pointer">Добавить</button>
                            </div>
                        </div>
                    </form>
                </footer>
            </li>
        @endforeach
    </ul>

    <div class="js-shopping-list-backdrop fixed inset-0 bg-black/50 opacity-0 pointer-events-none transition-opacity duration-150"></div>

    <aside class="js-shopping-list-panel fixed inset-y-0 right-0 w-full max-w-sm bg-white shadow-2xl translate-x-full transition-transform duration-150 z-20">
    </aside>

    @push('scripts')
        @vite('resources/js/week-menu/index.js')
    @endpush

    @push('styles')
        @vite('resources/css/week-menu.css')
    @endpush

</x-layouts.app>
