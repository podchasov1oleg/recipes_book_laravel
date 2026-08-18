<?php

namespace Tests\Feature;

use App\Models\MenuDay;
use App\Models\Product;
use App\Models\Recipe;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты функционала меню на неделю
 */
class WeekMenuTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверить, что без параметра monday отображается текущая
     * неделя (пн-вс), содержащая сегодняшнюю дату
     *
     * @return void
     */
    public function test_index_displays_current_week_by_default()
    {
        $response = $this->get('/week-menu');

        $response->assertStatus(200);

        // определить понедельник текущей недели
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $day = $monday->copy()->addDays($i);

            $response->assertSee($day->isoFormat('dd'));
            $response->assertSee($day->translatedFormat('j F'));
        }
    }

    /**
     * Проверить, что с параметром ?monday=... отображается
     * запрошенная неделя, а не текущая
     *
     * @return void
     */
    public function test_index_displays_selected_week_from_monday_param()
    {
        $param = '2026-08-10';

        $response = $this->get('/week-menu?monday=' . $param);

        $response->assertStatus(200);

        $monday = Carbon::parse($param)->startOfWeek(CarbonInterface::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $day = $monday->copy()->addDays($i);

            $response->assertSee($day->isoFormat('dd'));
            $response->assertSee($day->translatedFormat('j F'));
        }
    }

    /**
     * Проверить, что рецепт, привязанный к дню через MenuDay,
     * виден в соответствующей колонке
     *
     * @return void
     */
    public function test_index_displays_recipes_attached_to_day()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);
        $recipe = Recipe::factory()->create();

        $menuDay->recipes()->attach($recipe);

        $response = $this->get('/week-menu?monday=' . $monday);

        $response->assertStatus(200);

        $response->assertSee($recipe->title);
    }

    /**
     * Проверить, что день без привязанных рецептов показывает
     * сообщение «Пока ничего не запланировано»
     *
     * @return void
     */
    public function test_index_displays_empty_state_for_day_without_recipes()
    {
        $response = $this->get('/week-menu');

        $response->assertStatus(200);
        $response->assertSee('Пока ничего не запланировано');
    }

    /**
     * Проверить, что список рецептов для выбора у дня не содержит
     * рецептов, уже привязанных к этому дню, но при этом рецепт
     * остаётся доступным для выбора в остальных днях недели
     *
     * @return void
     */
    public function test_index_excludes_already_attached_recipes_from_day_select()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);
        $recipe = Recipe::factory()->create();
        $menuDay->recipes()->attach($recipe);

        $response = $this->get('/week-menu?monday=' . $monday);

        $optionHtml = '<option value="' . $recipe->id . '">';

        $this->assertSame(6, substr_count($response->getContent(), $optionHtml));
    }

    /**
     * Проверить, что рядом с рецептом в колонке дня отображается
     * корректное количество и склонение «N ингредиентов»
     *
     * @return void
     */
    public function test_index_displays_correct_products_count_for_recipe()
    {
        $productsCount = 3;
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);

        $recipe = Recipe::factory()->create();
        $products = Product::factory()->count($productsCount)->create();
        $recipe->products()->attach($products);
        $menuDay->recipes()->attach($recipe);

        $response = $this->get('/week-menu?monday=' . $monday);

        $response->assertSee($productsCount . ' ингредиента');
    }

    /**
     * Проверить, что для дня без существующей строки MenuDay она
     * создаётся при первом добавлении рецепта
     *
     * @return void
     */
    public function test_store_creates_menu_day_when_missing()
    {
        $recipe = Recipe::factory()->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $response->assertRedirect(route('week-menu'));

        $this->assertDatabaseHas('menu_days', ['day' => $day]);
    }

    /**
     * Проверить, что рецепт привязывается к MenuDay через
     * pivot-таблицу menu_day_recipe
     *
     * @return void
     */
    public function test_store_attaches_recipes_to_day()
    {
        $recipe = Recipe::factory()->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $menuDay = MenuDay::where('day', $day)->firstOrFail();

        $this->assertDatabaseHas('menu_day_recipe', ['recipe_id' => $recipe->id, 'menu_day_id' => $menuDay->id]);
    }

    /**
     * Проверить, что можно отправить сразу несколько recipe_ids
     * за один запрос, и все они привяжутся к дню
     *
     * @return void
     */
    public function test_store_attaches_multiple_recipes_at_once()
    {
        $recipes = Recipe::factory()->count(3)->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => $recipes->pluck('id')->toArray(),
            ]
        );

        $menuDay = MenuDay::where('day', $day)->firstOrFail();

        $response->assertRedirect(route('week-menu'));

        foreach ($recipes as $recipe) {
            $this->assertDatabaseHas('menu_day_recipe', ['recipe_id' => $recipe->id, 'menu_day_id' => $menuDay->id]);
        }
    }

    /**
     * Проверить, что добавление новых рецептов к дню, где уже
     * есть рецепты, не отвязывает существующие
     * (syncWithoutDetaching, а не sync/detach)
     *
     * @return void
     */
    public function test_store_does_not_detach_existing_recipes_when_adding_new_ones()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);

        $recipe = Recipe::factory()->create();
        $menuDay->recipes()->attach($recipe);

        $otherRecipe = Recipe::factory()->create();

        $response = $this->post(
            '/week-menu',
            [
                'day' => $monday->format('Y-m-d'),
                'recipe_ids' => [$otherRecipe->id],
            ]
        );

        $response->assertRedirect(route('week-menu'));

        $this->assertDatabaseHas('menu_day_recipe', ['recipe_id' => $recipe->id, 'menu_day_id' => $menuDay->id]);
        $this->assertDatabaseHas('menu_day_recipe', ['recipe_id' => $otherRecipe->id, 'menu_day_id' => $menuDay->id]);
    }

    /**
     * Проверить, что без поля day запрос не проходит валидацию
     *
     * @return void
     */
    public function test_store_requires_day()
    {
        $recipe = Recipe::factory()->create();

        $response = $this->post(
            '/week-menu',
            [
                'day' => null,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $response->assertSessionHasErrors('day');
    }

    /**
     * Проверить, что day в формате, отличном от Y-m-d,
     * отклоняется валидацией
     *
     * @return void
     */
    public function test_store_rejects_invalid_day_format()
    {
        $recipe = Recipe::factory()->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('d.m.Y');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $response->assertSessionHasErrors('day');
    }

    /**
     * Проверить, что без recipe_ids запрос не проходит валидацию
     *
     * @return void
     */
    public function test_store_requires_recipe_ids()
    {
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => null,
            ]
        );

        $response->assertSessionHasErrors('recipe_ids');
    }

    /**
     * Проверить, что пустой массив recipe_ids отклоняется
     * (required, а не просто отсутствие поля)
     *
     * @return void
     */
    public function test_store_rejects_empty_recipe_ids_array()
    {
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [],
            ]
        );

        $response->assertSessionHasErrors('recipe_ids');
    }

    /**
     * Проверить, что несуществующий id рецепта отклоняется
     * правилом exists, а не проходит валидацию
     *
     * @return void
     */
    public function test_store_rejects_nonexistent_recipe_id()
    {
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [0],
            ]
        );

        $response->assertSessionHasErrors('recipe_ids.0');
    }

    /**
     * Проверить редирект и flash-сообщение об успехе после
     * успешного добавления рецепта в меню
     *
     * @return void
     */
    public function test_store_redirects_with_success_message()
    {
        $recipe = Recipe::factory()->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $response->assertRedirect(route('week-menu'));

        $response->assertSessionHas('success');
    }

    /**
     * Проверить, что рецепт отвязывается от конкретного дня
     * (удаляется из pivot menu_day_recipe), а остальные рецепты
     * этого дня остаются привязанными
     *
     * @return void
     */
    public function test_destroy_detaches_recipe_from_day()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);
        $recipe = Recipe::factory()->create();
        $otherRecipe = Recipe::factory()->create();

        $menuDay->recipes()->attach([$recipe, $otherRecipe]);

        $response = $this->delete(
            route(
                'week-menu.destroy',
                [
                    'menuDay' => $menuDay,
                    'recipe' => $recipe,
                ]
            )
        );

        $response->assertRedirect(route('week-menu'));

        $this->assertDatabaseMissing(
            'menu_day_recipe',
            ['menu_day_id' => $menuDay->id, 'recipe_id' => $recipe->id]
        );

        $this->assertDatabaseHas(
            'menu_day_recipe',
            ['menu_day_id' => $menuDay->id, 'recipe_id' => $otherRecipe->id]
        );
    }

    /**
     * Проверить, что сама строка MenuDay не удаляется при
     * отвязке рецепта, даже если это был последний рецепт дня
     *
     * @return void
     */
    public function test_destroy_does_not_delete_menu_day_row()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);
        $recipe = Recipe::factory()->create();

        $menuDay->recipes()->attach([$recipe]);

        $response = $this->delete(
            route(
                'week-menu.destroy',
                [
                    'menuDay' => $menuDay,
                    'recipe' => $recipe,
                ]
            )
        );

        $this->assertDatabaseHas('menu_days', ['day' => $monday->format('Y-m-d')]);
    }

    /**
     * Проверить, что рецепт, привязанный к нескольким дням, при
     * отвязке от одного дня остаётся привязанным к другим
     *
     * @return void
     */
    public function test_destroy_does_not_affect_recipe_attached_to_other_days()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $tuesday = $monday->copy()->addDay();

        $mondayMenuDay = MenuDay::factory()->create(['day' => $monday]);
        $tuesdayMenuDay = MenuDay::factory()->create(['day' => $tuesday]);
        $recipe = Recipe::factory()->create();
        $otherRecipe = Recipe::factory()->create();

        $mondayMenuDay->recipes()->attach([$recipe]);
        $tuesdayMenuDay->recipes()->attach([$otherRecipe]);

        $this->delete(
            route(
                'week-menu.destroy',
                [
                    'menuDay' => $mondayMenuDay,
                    'recipe' => $recipe,
                ]
            )
        );

        $this->assertDatabaseMissing(
            'menu_day_recipe',
            ['menu_day_id' => $mondayMenuDay->id, 'recipe_id' => $recipe->id]
        );

        $this->assertDatabaseHas(
            'menu_day_recipe',
            ['menu_day_id' => $tuesdayMenuDay->id, 'recipe_id' => $otherRecipe->id]
        );
    }

    /**
     * Проверить редирект и flash-сообщение об успехе после
     * удаления рецепта из дня
     *
     * @return void
     */
    public function test_destroy_redirects_with_success_message()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->create(['day' => $monday]);
        $recipe = Recipe::factory()->create();

        $menuDay->recipes()->attach([$recipe]);

        $response = $this->delete(
            route(
                'week-menu.destroy',
                [
                    'menuDay' => $menuDay,
                    'recipe' => $recipe,
                ]
            )
        );

        $response->assertRedirect(route('week-menu'));
        $response->assertSessionHas('success');
    }
}
