<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuDayRequest;
use App\Models\MenuDay;
use App\Models\Recipe;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;

/**
 * Контроллер страницы меню на неделю
 */
class WeekMenuController extends Controller
{
    /**
     * Страница меню на неделю
     */
    public function index()
    {
        $monday = request()->query('monday')
            ? Carbon::parse(request()->query('monday'))->startOfWeek(CarbonInterface::MONDAY)
            : Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $sunday = $monday->copy()->addDays(6);

        $menuDays = MenuDay::whereBetween('day', [$monday, $sunday])
            ->with(['recipes' => fn ($query) => $query->withCount('products')])
            ->get()
            ->keyBy(fn ($menuDay) => $menuDay->day->format('Y-m-d'));

        // выставить отсутствующие дни недели
        for ($i = 0; $i < 7; $i++) {
            $day = $monday->copy()->addDays($i);

            if (empty($menuDays[$day->format('Y-m-d')])) {
                $menuDays[$day->format('Y-m-d')] = (new MenuDay(['day' => $day]))
                    ->setRelation('recipes', collect());
            }
        }

        // сделать сортировку по дням
        $menuDays = $menuDays->sortKeys();

        // теперь надо собрать все рецепты и отфильтровать те, которых нет для определенного дня
        $allRecipes = Recipe::all();
        $recipeDays = [];
        foreach ($menuDays->keys() as $day) {
            $recipeDays[$day] = $allRecipes->diff($menuDays[$day]->recipes);
        }

        return view('week-menu.index', compact('monday', 'sunday', 'menuDays', 'recipeDays'));
    }

    /**
     * Обработать запрос на сохранение рецепта/рецептов для дня меню
     * @param StoreMenuDayRequest $request
     * @return RedirectResponse
     */
    public function store(StoreMenuDayRequest $request)
    {
        $validated = $request->validated();

        $menuDay = MenuDay::firstOrCreate(['day' => $validated['day']]);

        $menuDay->recipes()->syncWithoutDetaching($validated['recipe_ids']);

        return redirect()
            ->route('week-menu')
            ->with('success', 'Рецепт успешно создан');
    }

    /**
     * @param MenuDay $menuDay
     * @param Recipe $recipe
     * @return RedirectResponse
     */
    public function destroy(MenuDay $menuDay, Recipe $recipe)
    {
        $menuDay->recipes()->detach($recipe);

        return redirect()
            ->route('week-menu')
            ->with('success', 'Рецепт успешно удален');
    }
}
