<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\MenuDay;
use App\Models\Product;
use App\Models\Recipe;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Tests\TestCase;

/**
 * Тесты функционала меню на неделю
 */
class WeekMenuTest extends TestCase
{
    /**
     * Проверить, что без параметра monday отображается текущая
     * неделя (пн-вс), содержащая сегодняшнюю дату
     *
     * @return void
     */
    public function test_index_displays_current_week_by_default()
    {
        $response = $this->actingAs($this->user)->get('/week-menu');

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

        $response = $this->actingAs($this->user)->get('/week-menu?monday='.$param);

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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $recipe = Recipe::factory()->for($this->user->household)->create();

        $menuDay->recipes()->attach($recipe);

        $response = $this->actingAs($this->user)->get('/week-menu?monday='.$monday);

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
        $response = $this->actingAs($this->user)->get('/week-menu');

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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $menuDay->recipes()->attach($recipe);

        $response = $this->actingAs($this->user)->get('/week-menu?monday='.$monday);

        $optionHtml = '<option value="'.$recipe->id.'">';

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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        $recipe = Recipe::factory()->for($this->user->household)->create();
        $products = Product::factory()->count($productsCount)->create();
        $recipe->products()->attach($products);
        $menuDay->recipes()->attach($recipe);

        $response = $this->actingAs($this->user)->get('/week-menu?monday='.$monday);

        $response->assertSee($productsCount.' ингредиента');
    }

    /**
     * Проверить, что для дня без существующей строки MenuDay она
     * создаётся при первом добавлении рецепта
     *
     * @return void
     */
    public function test_store_creates_menu_day_when_missing()
    {
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($this->user)->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $response->assertRedirect(route('week-menu'));

        $this->assertDatabaseHas('menu_days', ['day' => $day, 'household_id' => $this->user->household->id]);
    }

    /**
     * Проверить, что рецепт привязывается к MenuDay через
     * pivot-таблицу menu_day_recipe
     *
     * @return void
     */
    public function test_store_attaches_recipes_to_day()
    {
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($this->user)->post(
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
        $recipes = Recipe::factory()->for($this->user->household)->count(3)->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($this->user)->post(
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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        $recipe = Recipe::factory()->for($this->user->household)->create();
        $menuDay->recipes()->attach($recipe);

        $otherRecipe = Recipe::factory()->for($this->user->household)->create();

        $response = $this->actingAs($this->user)->post(
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
        $recipe = Recipe::factory()->for($this->user->household)->create();

        $response = $this->actingAs($this->user)->post(
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
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('d.m.Y');

        $response = $this->actingAs($this->user)->post(
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

        $response = $this->actingAs($this->user)->post(
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

        $response = $this->actingAs($this->user)->post(
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

        $response = $this->actingAs($this->user)->post(
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
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($this->user)->post(
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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $otherRecipe = Recipe::factory()->for($this->user->household)->create();

        $menuDay->recipes()->attach([$recipe, $otherRecipe]);

        $response = $this->actingAs($this->user)->delete(
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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $recipe = Recipe::factory()->for($this->user->household)->create();

        $menuDay->recipes()->attach([$recipe]);

        $this->actingAs($this->user)->delete(
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

        $mondayMenuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $tuesdayMenuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $tuesday]);
        $recipe = Recipe::factory()->for($this->user->household)->create();
        $otherRecipe = Recipe::factory()->for($this->user->household)->create();

        $mondayMenuDay->recipes()->attach([$recipe]);
        $tuesdayMenuDay->recipes()->attach([$otherRecipe]);

        $this->actingAs($this->user)->delete(
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

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $recipe = Recipe::factory()->for($this->user->household)->create();

        $menuDay->recipes()->attach([$recipe]);

        $response = $this->actingAs($this->user)->delete(
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

    /**
     * Проверить, что список покупок содержит продукты рецептов,
     * привязанных к дням выбранной недели
     *
     * @return void
     */
    public function test_shopping_list_returns_products_from_recipes_of_selected_week()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        $recipe = Recipe::factory()->for($this->user->household)->create();
        $products = Product::factory()->count(2)->create();
        $recipe->products()->attach($products);
        $menuDay->recipes()->attach($recipe);

        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        $response->assertOk();

        foreach ($products as $product) {
            $response->assertSee($product->title);
        }
    }

    /**
     * Проверить, что продукт рецепта, привязанного к дню вне
     * выбранной недели, не попадает в список покупок
     *
     * @return void
     */
    public function test_shopping_list_excludes_products_from_other_weeks()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $prevMonday = $monday->copy()->subWeek();

        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $prevMenuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $prevMonday]);

        $recipe = Recipe::factory()->for($this->user->household)->create();
        $products = Product::factory()->count(2)->create();
        $recipe->products()->attach($products);
        $menuDay->recipes()->attach($recipe);

        $otherRecipe = Recipe::factory()->for($this->user->household)->create();
        $otherProducts = Product::factory()->count(2)->create();
        $otherRecipe->products()->attach($otherProducts);
        $prevMenuDay->recipes()->attach($otherRecipe);

        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        foreach ($products as $product) {
            $response->assertSee('>'.$product->title.'<', false);
        }

        foreach ($otherProducts as $otherProduct) {
            $response->assertDontSee('>'.$otherProduct->title.'<', false);
        }
    }

    /**
     * Проверить, что продукт, входящий сразу в два рецепта
     * выбранной недели, отображается в списке покупок один раз,
     * а его количество — сумма пересчитанных по порциям величин
     * из обоих рецептов
     *
     * @return void
     */
    public function test_shopping_list_deduplicates_product_shared_between_recipes()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 2]);
        $otherRecipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        $recipe->products()->attach([
            $product1->id => ['quantity' => 1],
            $product2->id => ['quantity' => 2],
        ]);
        $otherRecipe->products()->attach([
            $product2->id => ['quantity' => 3],
            $product3->id => ['quantity' => 4],
        ]);

        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 2],
            $otherRecipe->id => ['servings' => 4],
        ]);

        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        $this->assertSame(
            1,
            substr_count($response->getContent(), '>'.$product2->title.'<'),
        );

        $response->assertSee('>5 '.$product2->unit->value.'</span>', false);
    }

    /**
     * Проверить, что для недели без запланированных рецептов
     * список покупок отображается пустым, без ошибок
     *
     * @return void
     */
    public function test_shopping_list_returns_empty_when_no_recipes_planned()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        $response->assertDontSee('<li>', false);
    }

    /**
     * @return void
     */
    public function test_shopping_list_scales_quantity_by_recipe_and_day_servings()
    {
        $product = Product::factory()->create();
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 2]);
        $recipe->products()->attach([
            $product->id => ['quantity' => 100],
        ]);

        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 4],
        ]);

        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        $response->assertSee('>200 '.$product->unit->value.'</span>', false);
    }

    /**
     * Проверить, что продукт, входящий в два разных рецепта одной
     * недели, суммируется по количеству, пересчитанному отдельно
     * для каждого рецепта (разные `servings`/`quantity`)
     *
     * @return void
     */
    public function test_shopping_list_sums_quantity_of_shared_product_across_recipes()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт 1
        $recipe1 = Recipe::factory()->for($this->user->household)->create(['servings' => 2]);
        // рецепт 2
        $recipe2 = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецептам
        $recipe1->products()->attach([
            $product->id => ['quantity' => 10],
        ]);
        $recipe2->products()->attach([
            $product->id => ['quantity' => 20],
        ]);

        // создать дни меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $tuesday = $monday->copy()->addDay();

        $menuDay1 = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $menuDay2 = MenuDay::factory()->for($this->user->household)->create(['day' => $tuesday]);

        // прикрепить рецепты
        $menuDay1->recipes()->attach([
            $recipe1->id => ['servings' => 6],
        ]);
        $menuDay2->recipes()->attach([
            $recipe2->id => ['servings' => 8],
        ]);

        // отправить запрос на получение списка продуктов
        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        $response->assertSee('>70 '.$product->unit->value.'</span>', false);
    }

    /**
     * Проверить, что один и тот же рецепт, назначенный на разные дни
     * одной недели, суммирует свой вклад в продукт по всем дням,
     * а не только за один из них
     *
     * @return void
     */
    public function test_shopping_list_sums_quantity_of_product_across_multiple_days()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 2]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать дни меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $tuesday = $monday->copy()->addDay();

        $menuDay1 = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $menuDay2 = MenuDay::factory()->for($this->user->household)->create(['day' => $tuesday]);

        // прикрепить рецепты
        $menuDay1->recipes()->attach([
            $recipe->id => ['servings' => 6],
        ]);
        $menuDay2->recipes()->attach([
            $recipe->id => ['servings' => 8],
        ]);

        // отправить запрос на получение списка продуктов
        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        $response->assertSee('>70 '.$product->unit->value.'</span>', false);
    }

    /**
     * Проверить, что дробный результат пересчёта количества
     * округляется (`round()`) при выводе в список покупок
     *
     * @return void
     */
    public function test_shopping_list_rounds_fractional_quantity()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 1],
        ]);

        // отправить запрос на получение списка продуктов
        $response = $this->actingAs($this->user)->get(
            route(
                'week-menu.shopping-list',
                [
                    'monday' => $monday->format('Y-m-d'),
                ]
            )
        );

        // 10 / 4 * 1 = 2.5, но после round будет 3
        $response->assertSee('>3 '.$product->unit->value.'</span>', false);
    }

    /**
     * Проверить, что `action=inc` увеличивает `menu_day_recipe.servings`
     * на 1 и возвращает новое значение в JSON-ответе
     *
     * @return void
     */
    public function test_update_servings_increments_pivot_value()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 2],
        ]);

        // отправить запрос на увеличение servings

        $response = $this->actingAs($this->user)->patch(
            route('week-menu.update-servings', ['menuDay' => $menuDay, 'recipe' => $recipe]),
            ['action' => 'inc']
        );

        $response->assertJson(['servings' => 3]);

        $this->assertDatabaseHas('menu_day_recipe', [
            'menu_day_id' => $menuDay->id,
            'recipe_id' => $recipe->id,
            'servings' => 3,
        ]);
    }

    /**
     * Проверить, что `action=dec` уменьшает `menu_day_recipe.servings`
     * на 1 и возвращает новое значение в JSON-ответе
     *
     * @return void
     */
    public function test_update_servings_decrements_pivot_value()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 2],
        ]);

        // отправить запрос на уменьшение servings
        $response = $this->actingAs($this->user)->patch(
            route('week-menu.update-servings', ['menuDay' => $menuDay, 'recipe' => $recipe]),
            ['action' => 'dec']
        );

        $response->assertJson(['servings' => 1]);

        $this->assertDatabaseHas('menu_day_recipe', [
            'menu_day_id' => $menuDay->id,
            'recipe_id' => $recipe->id,
            'servings' => 1,
        ]);
    }

    /**
     * Проверить, что при `servings=255` `action=inc` не увеличивает
     * значение дальше максимума
     *
     * @return void
     */
    public function test_update_servings_does_not_exceed_max()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 255],
        ]);

        // отправить запрос на изменение servings
        $response = $this->actingAs($this->user)->patch(
            route('week-menu.update-servings', ['menuDay' => $menuDay, 'recipe' => $recipe]),
            ['action' => 'inc']
        );

        $response->assertJson(['servings' => 255]);

        $this->assertDatabaseHas('menu_day_recipe', [
            'menu_day_id' => $menuDay->id,
            'recipe_id' => $recipe->id,
            'servings' => 255,
        ]);
    }

    /**
     * Проверить, что при `servings=1` `action=dec` не уменьшает
     * значение ниже минимума
     *
     * @return void
     */
    public function test_update_servings_does_not_go_below_min()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 1],
        ]);

        // отправить запрос на изменение servings
        $response = $this->actingAs($this->user)->patch(
            route('week-menu.update-servings', ['menuDay' => $menuDay, 'recipe' => $recipe]),
            ['action' => 'dec']
        );

        $response->assertJson(['servings' => 1]);

        $this->assertDatabaseHas('menu_day_recipe', [
            'menu_day_id' => $menuDay->id,
            'recipe_id' => $recipe->id,
            'servings' => 1,
        ]);
    }

    /**
     * Проверить, что `action`, отличный от `inc`/`dec`, отклоняется
     * валидацией и не меняет `servings` в БД
     *
     * @return void
     */
    public function test_update_servings_requires_valid_action()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 1],
        ]);

        // отправить запрос на изменение servings (как реальный axios.patch —
        // с X-Requested-With, чтобы Laravel вернул 422 JSON, а не редирект)
        $response = $this->actingAs($this->user)->patchJson(
            route('week-menu.update-servings', ['menuDay' => $menuDay, 'recipe' => $recipe]),
            ['action' => 'some']
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('action');

        $this->assertDatabaseHas('menu_day_recipe', [
            'menu_day_id' => $menuDay->id,
            'recipe_id' => $recipe->id,
            'servings' => 1,
        ]);
    }

    /**
     * Проверить, что запрос на изменение `servings` для рецепта,
     * не привязанного к переданному дню меню, возвращает 404
     *
     * @return void
     */
    public function test_update_servings_returns_404_for_recipe_not_attached_to_day()
    {
        // продукт
        $product = Product::factory()->create();
        // рецепт
        $recipe = Recipe::factory()->for($this->user->household)->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        $unattachedRecipe = Recipe::factory()->for($this->user->household)->create(['servings' => 6]);
        $unattachedRecipe->products()->attach([
            $product->id => ['quantity' => 20],
        ]);

        // создать день меню
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $menuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);

        // прикрепить рецепты
        $menuDay->recipes()->attach([
            $recipe->id => ['servings' => 1],
        ]);

        // отправить запрос на изменение servings
        $response = $this->actingAs($this->user)->patch(
            route('week-menu.update-servings', ['menuDay' => $menuDay, 'recipe' => $unattachedRecipe]),
            ['action' => 'inc']
        );

        $response->assertNotFound();
    }

    /**
     * Убедиться, что в меню недели нет дней и рецептов другого домохозяйства
     *
     * @return void
     */
    public function test_index_displays_only_own_household_menu()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $otherHousehold = Household::factory()->create();

        $otherMenuDay = MenuDay::factory()->for($otherHousehold)->create(['day' => $monday]);
        $otherRecipe = Recipe::factory()->for($otherHousehold)->create(['title' => 'Чужой рецепт в меню']);
        $otherMenuDay->recipes()->attach($otherRecipe);

        $response = $this->actingAs($this->user)->get('/week-menu?monday='.$monday->format('Y-m-d'));

        $response->assertOk();
        $response->assertDontSee('Чужой рецепт в меню');
    }

    /**
     * Убедиться, что в выпадающем списке рецептов для дня только свои рецепты
     *
     * @return void
     */
    public function test_index_day_select_offers_only_own_household_recipes()
    {
        $ownRecipe = Recipe::factory()->for($this->user->household)->create(['title' => 'Свой рецепт']);
        $otherRecipe = Recipe::factory()->for(Household::factory())->create(['title' => 'Чужой рецепт']);

        $response = $this->actingAs($this->user)->get('/week-menu');

        $response->assertOk();

        // по одному option на каждый из 7 дней недели
        $content = $response->getContent();
        $this->assertSame(7, substr_count($content, '<option value="'.$ownRecipe->id.'">'));
        $this->assertSame(0, substr_count($content, '<option value="'.$otherRecipe->id.'">'));
    }

    /**
     * Убедиться, что два домохозяйства могут запланировать меню на одну дату
     *
     * @return void
     */
    public function test_store_allows_same_day_in_different_households()
    {
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');
        $otherMenuDay = MenuDay::factory()->for(Household::factory())->create(['day' => $day]);
        $recipe = Recipe::factory()->for($this->user->household)->create();

        $response = $this->actingAs($this->user)->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('week-menu'));

        $this->assertDatabaseHas('menu_days', ['day' => $day, 'household_id' => $this->user->household->id]);
        $this->assertDatabaseHas('menu_days', ['day' => $day, 'household_id' => $otherMenuDay->household_id]);
        $this->assertDatabaseCount('menu_days', 2);
    }

    /**
     * Убедиться, что рецепт не прицепляется к дню другого домохозяйства
     * на ту же дату
     *
     * @return void
     */
    public function test_store_does_not_attach_to_other_household_menu_day()
    {
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');
        $otherMenuDay = MenuDay::factory()->for(Household::factory())->create(['day' => $day]);
        $recipe = Recipe::factory()->for($this->user->household)->create();

        $this->actingAs($this->user)->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$recipe->id],
            ]
        );

        $ownMenuDay = $this->user->household->menuDays()->where('day', $day)->firstOrFail();

        $this->assertDatabaseHas('menu_day_recipe', ['menu_day_id' => $ownMenuDay->id, 'recipe_id' => $recipe->id]);
        $this->assertDatabaseMissing('menu_day_recipe', ['menu_day_id' => $otherMenuDay->id]);
    }

    /**
     * Убедиться, что id чужого рецепта не проходит валидацию
     *
     * @return void
     */
    public function test_store_rejects_other_household_recipe_id()
    {
        $day = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');
        $otherRecipe = Recipe::factory()->for(Household::factory())->create();

        $response = $this->actingAs($this->user)->post(
            '/week-menu',
            [
                'day' => $day,
                'recipe_ids' => [$otherRecipe->id],
            ]
        );

        $response->assertSessionHasErrors('recipe_ids.0');

        $this->assertDatabaseMissing('menu_day_recipe', ['recipe_id' => $otherRecipe->id]);
    }

    /**
     * Убедиться, что открепить рецепт от чужого дня нельзя: 403, pivot не изменился
     *
     * @return void
     */
    public function test_destroy_forbidden_for_other_household_menu_day()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $otherHousehold = Household::factory()->create();

        $otherMenuDay = MenuDay::factory()->for($otherHousehold)->create(['day' => $monday]);
        $otherRecipe = Recipe::factory()->for($otherHousehold)->create();
        $otherMenuDay->recipes()->attach($otherRecipe);

        $response = $this->actingAs($this->user)->delete(
            route('week-menu.destroy', ['menuDay' => $otherMenuDay, 'recipe' => $otherRecipe])
        );

        $response->assertForbidden();

        $this->assertDatabaseHas(
            'menu_day_recipe',
            ['menu_day_id' => $otherMenuDay->id, 'recipe_id' => $otherRecipe->id]
        );
    }

    /**
     * Убедиться, что изменить порции в чужом дне нельзя: 403, `servings` не изменились
     *
     * @return void
     */
    public function test_update_servings_forbidden_for_other_household_menu_day()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $otherHousehold = Household::factory()->create();

        $otherMenuDay = MenuDay::factory()->for($otherHousehold)->create(['day' => $monday]);
        $otherRecipe = Recipe::factory()->for($otherHousehold)->create();
        $otherMenuDay->recipes()->attach([$otherRecipe->id => ['servings' => 2]]);

        $response = $this->actingAs($this->user)->patch(
            route('week-menu.update-servings', ['menuDay' => $otherMenuDay, 'recipe' => $otherRecipe]),
            ['action' => 'inc']
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('menu_day_recipe', [
            'menu_day_id' => $otherMenuDay->id,
            'recipe_id' => $otherRecipe->id,
            'servings' => 2,
        ]);
    }

    /**
     * Убедиться, что в список покупок не попадают продукты из меню
     * другого домохозяйства
     *
     * @return void
     */
    public function test_shopping_list_includes_only_own_household_recipes()
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        // своё меню
        $ownMenuDay = MenuDay::factory()->for($this->user->household)->create(['day' => $monday]);
        $ownRecipe = Recipe::factory()->for($this->user->household)->create();
        $ownRecipe->products()->attach([
            Product::factory()->create(['title' => 'Свой продукт'])->id => ['quantity' => 1],
        ]);
        $ownMenuDay->recipes()->attach($ownRecipe);

        // чужое меню на ту же неделю
        $otherHousehold = Household::factory()->create();
        $otherMenuDay = MenuDay::factory()->for($otherHousehold)->create(['day' => $monday]);
        $otherRecipe = Recipe::factory()->for($otherHousehold)->create();
        $otherRecipe->products()->attach([
            Product::factory()->create(['title' => 'Чужой продукт'])->id => ['quantity' => 1],
        ]);
        $otherMenuDay->recipes()->attach($otherRecipe);

        $response = $this->actingAs($this->user)->get(
            route('week-menu.shopping-list', ['monday' => $monday->format('Y-m-d')])
        );

        $response->assertOk();
        $response->assertSee('Свой продукт');
        $response->assertDontSee('Чужой продукт');
    }
}
