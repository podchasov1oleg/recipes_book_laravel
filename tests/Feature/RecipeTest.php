<?php

namespace Tests\Feature;

use App\Enums\Unit;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\TestCase;

/**
 * Тесты функционала рецептов
 */
class RecipeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверить, что на странице списка рецептов виден созданный рецепт
     *
     * @return void
     */
    public function test_index_displays_recipes()
    {
        $recipe = Recipe::factory()->create();

        $response = $this->get(route('recipes.index'));

        $response->assertStatus(200);

        $response->assertSee($recipe->title);
    }

    /**
     * Убедиться, что число ингредиентов и его склонение (1/2/5) на странице
     * списка соответствуют реальному количеству привязанных продуктов
     *
     * @return void
     */
    public function test_index_displays_correct_products_count()
    {
        Product::factory()->count(10)->create();

        Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(1)->get())
            ->create();

        Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(2)->get())
            ->create();

        Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(5)->get())
            ->create();

        $response = $this->get(route('recipes.index'));

        $response->assertStatus(200);

        $response->assertSee('1 ингредиент');

        $response->assertSee('2 ингредиента');

        $response->assertSee('5 ингредиентов');
    }

    /**
     * Проверить, что форма создания рецепта содержит поле названия,
     * мультиселект продуктов и все продукты в виде опций
     *
     * @return void
     */
    public function test_create_displays_form()
    {
        $products = Product::factory()->count(10)->create();

        $response = $this->get(route('recipes.create'));

        $response->assertStatus(200);

        $response->assertSee('name="title"', false);

        $response->assertSee('name="products"', false);

        foreach ($products as $product) {
            $response->assertSee($product->title);
        }
    }

    /**
     * Проверить, что рецепт создаётся и привязывается к выбранному продукту
     *
     * @return void
     */
    public function test_store_creates_recipe()
    {
        $product = Product::factory()->create();

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode([
                    [
                        'product_id' => $product->id,
                        'unit' => $product->unit->value,
                        'quantity' => 1,
                        'title' => $product->title,
                    ]
                ]),
                'servings' => 2,
            ],
        );

        $response->assertRedirect(route('recipes.index'));

        $recipe = Recipe::where('title', 'Test Recipe')->firstOrFail();

        $this->assertDatabaseHas('recipes', [
            'title' => 'Test Recipe',
            'servings' => 2,
        ]);

        $this->assertDatabaseHas('product_recipe', [
            'product_id' => $product->id,
            'recipe_id' => $recipe->id,
            'quantity' => 1,
        ]);
    }

    /**
     * Убедиться, что все переданные product_ids попадают в pivot-таблицу
     * product_recipe, а не только один из них
     *
     * @return void
     */
    public function test_store_attaches_selected_products()
    {
        $products = Product::factory()->count(3)->create();

        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'quantity' => 1,
            'title' => $product->title,
        ]);

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 2,
            ],
        );

        $response->assertRedirect(route('recipes.index'));

        $recipe = Recipe::where('title', 'Test Recipe')->firstOrFail();

        foreach ($products as $product) {
            $this->assertDatabaseHas('product_recipe', [
                'product_id' => $product->id,
                'recipe_id' => $recipe->id,
                'quantity' => 1,
            ]);
        }
    }

    /**
     * Проверить, что без названия рецепт не создаётся
     *
     * @return void
     */
    public function test_store_requires_title()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => '',
                'product_ids' => $products->pluck('id')->all(),
            ],
        );

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('recipes', ['title' => '']);
    }

    /**
     * Проверить, что слишком длинное название (256 символов) не проходит
     * валидацию
     *
     * @return void
     */
    public function test_store_title_max_length()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => str_repeat('a', 256),
                'product_ids' => $products->pluck('id')->all(),
            ],
        );

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Убедиться, что нельзя создать рецепт с названием, которое уже занято
     * другим рецептом
     *
     * @return void
     */
    public function test_store_title_must_be_unique()
    {
        $products = Product::factory()->count(3)->create();

        $recipe = Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(1)->get())
            ->create();

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => $recipe->title,
                'product_ids' => $products->pluck('id')->all(),
            ],
        );

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseCount('recipes', 1);
    }

    /**
     * Проверить, что без product_ids рецепт не создаётся
     *
     * @return void
     */
    public function test_store_requires_product_ids()
    {
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'servings' => 2,
            ],
        );

        $response->assertSessionHasErrors('products');

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Убедиться, что пустой массив products отклоняется валидацией
     * (для required пустой массив тоже считается непройденным, отдельного
     * сообщения от min:1 тут не появляется — по факту дублирует
     * test_store_requires_product_ids на уровне правил, но проверяет
     * другой payload: products передан, но пуст, а не отсутствует вовсе)
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_store_rejects_empty_product_ids_array()
    {
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode([]),
                'servings' => 2,
            ],
        );

        $response->assertSessionHasErrors('products');

        $messages = session('errors')->get('products');

        $this->assertTrue(
            collect($messages)->contains(fn ($message) => str_contains($message, 'Необходимо добавить хотя бы один продукт')),
        );
    }

    /**
     * Убедиться, что несуществующий id продукта отклоняется правилом exists,
     * а не проходит валидацию
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_store_rejects_nonexistent_product_id()
    {
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode([
                    [
                        'product_id' => 1,
                        'quantity' => 1,
                        'title' => 'Test Product',
                        'unit' => Unit::Gram->value,
                    ]
                ]),
            ],
        );

        $response->assertSessionHasErrors('products.0.product_id');

        $messages = session('errors')->get('products.0.product_id');

        $this->assertTrue(
            collect($messages)->contains(
                fn ($message) => str_contains($message, 'Выбранное значение поля')
                    && str_contains($message, 'недопустимо'),
            ),
        );
    }

    /**
     * Проверить, что quantity каждого продукта сохраняется в
     * pivot-таблице product_recipe
     *
     * @return void
     */
    public function test_store_saves_product_quantity()
    {
        $products = Product::factory()->count(3)->create();
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'quantity' => 1,
            'title' => $product->title,
        ]);

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 2,
            ],
        );

        $response->assertRedirect(route('recipes.index'));

        $recipe = Recipe::where('title', 'Test Recipe')->firstOrFail();

        foreach ($productsData as $productData) {
            $this->assertDatabaseHas('product_recipe', [
                'product_id' => $productData['product_id'],
                'recipe_id' => $recipe->id,
                'quantity' => $productData['quantity'],
            ]);
        }
    }

    /**
     * Проверить, что без quantity у продукта рецепт не создаётся
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_store_requires_product_quantity()
    {
        $products = Product::factory()->count(3)->create();
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
        ]);

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 2,
            ],
        );

        $response->assertSessionHasErrors('products.0.quantity');
        $response->assertSessionHasErrors('products.1.quantity');
        $response->assertSessionHasErrors('products.2.quantity');

        $messages = session('errors')->get('products.0.quantity');

        $this->assertTrue(
            collect($messages)->contains(fn ($message) => str_contains($message, 'Введите количество')),
        );

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Проверить, что нецелое значение quantity отклоняется валидацией
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_store_rejects_non_integer_product_quantity()
    {
        $products = Product::factory()->count(3)->create();
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
            'quantity' => 'string',
        ]);

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 2,
            ],
        );

        $response->assertSessionHasErrors('products.0.quantity');
        $response->assertSessionHasErrors('products.1.quantity');
        $response->assertSessionHasErrors('products.2.quantity');

        $messages = session('errors')->get('products.0.quantity');

        $this->assertTrue(
            collect($messages)->contains(fn ($message) => str_contains($message, 'Количество должно быть целым числом')),
        );

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Проверить, что quantity меньше 1 отклоняется правилом min:1
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_store_rejects_product_quantity_below_min()
    {
        $products = Product::factory()->count(3)->create();
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
            'quantity' => 0,
        ]);

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 2,
            ],
        );

        $response->assertSessionHasErrors('products.0.quantity');
        $response->assertSessionHasErrors('products.1.quantity');
        $response->assertSessionHasErrors('products.2.quantity');

        $messages = session('errors')->get('products.0.quantity');

        $this->assertTrue(
            collect($messages)->contains(fn ($message) => str_contains($message, 'Количество должно быть не менее')),
        );

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Проверить, что форма редактирования отмечает привязанные к рецепту
     * продукты как selected, а непривязанные - нет
     *
     * @return void
     */
    public function test_edit_displays_form_with_existing_data()
    {
        $products = Product::factory()->count(3)->create();
        $otherProduct = Product::factory()->create();

        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            $products->mapWithKeys(fn ($product) => [$product->id => ['quantity' => 100]])
        );

        $response = $this->get(route('recipes.edit', $recipe));

        preg_match(
            "/x-data=\"recipeForm\(JSON\.parse\('(.*?)'\)/s",
            $response->getContent(),
            $matches,
        );

        $initialProducts = json_decode(json_decode('"'.$matches[1].'"'), true);

        $initialProductIds = collect($initialProducts)->pluck('product_id');

        foreach ($products as $product) {
            $this->assertContains($product->id, $initialProductIds);
        }

        $this->assertNotContains($otherProduct->id, $initialProductIds);
    }

    /**
     * Проверить, что название рецепта успешно изменяется
     *
     * @return void
     */
    public function test_update_changes_title()
    {
        $product = Product::factory()->create();
        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            [$product->id => ['quantity' => 100]],
        );

        $oldTitle = $recipe->title;

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => 'Test Recipe',
                'products' => json_encode([
                    [
                        'product_id' => $product->id,
                        'quantity' => 100,
                        'title' => $product->title,
                        'unit' => $product->unit->value,
                    ],
                ]),
                'servings' => $recipe->servings,
            ]
        );

        $response->assertRedirect(route('recipes.index'));

        $this->assertDatabaseHas('recipes', ['title' => 'Test Recipe', 'id' => $recipe->id]);

        $this->assertDatabaseMissing('recipes', ['title' => $oldTitle]);
    }

    /**
     * Убедиться, что повторная отправка того же названия при обновлении
     * не считается конфликтом уникальности (Rule::unique()->ignore())
     *
     * @return void
     */
    public function test_update_without_changing_title_does_not_fail()
    {
        $products = Product::factory()->count(3)->create();
        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            $products->mapWithKeys(fn ($product) => [$product->id => ['quantity' => 100]])
        );

        $productsData = $products->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'quantity' => 100,
            'title' => $product->title,
        ]);

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'products' => json_encode($productsData),
                'servings' => $recipe->servings,
            ],
        );

        $response->assertRedirect(route('recipes.index'));

        $response->assertSessionDoesntHaveErrors();
    }

    /**
     * Убедиться, что нельзя переименовать рецепт в название, занятое другим
     * рецептом
     *
     * @return void
     */
    public function test_update_title_conflicts_with_another_recipe()
    {
        $products = Product::factory()->count(3)->create();

        $recipe = Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(1)->get())
            ->create();

        $otherRecipe = Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(2)->get())
            ->create();

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $otherRecipe->title,
                'product_ids' => $products->pluck('id')->all(),
            ]
        );

        $response->assertSessionHasErrors('title');
    }

    /**
     * Проверить, что update() синхронизирует продукты рецепта: новые
     * product_ids привязываются
     *
     * @return void
     */
    public function test_update_syncs_products()
    {
        $products = Product::factory()->count(4)->create();
        $otherProducts = Product::factory()->count(2)->create();

        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            $products->mapWithKeys(fn ($product) => [$product->id => ['quantity' => 100]])
        );

        foreach ($products as $product) {
            $this->assertDatabaseHas('product_recipe', [
                'product_id' => $product->id,
                'recipe_id' => $recipe->id,
                'quantity' => 100,
            ]);
        }

        $otherProductsData = $otherProducts->map(fn ($otherProduct) => [
            'product_id' => $otherProduct->id,
            'unit' => $otherProduct->unit->value,
            'quantity' => 1,
            'title' => $otherProduct->title,
        ]);

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'products' => json_encode($otherProductsData),
                'servings' => $recipe->servings,
            ]
        );

        $response->assertRedirect(route('recipes.index'));

        foreach ($otherProducts as $product) {
            $this->assertDatabaseHas('product_recipe', [
                'product_id' => $product->id,
                'recipe_id' => $recipe->id,
                'quantity' => 1,
            ]);
        }

        foreach ($products as $product) {
            $this->assertDatabaseMissing('product_recipe', [
                'product_id' => $product->id,
                'recipe_id' => $recipe->id,
                'quantity' => 100,
            ]);
        }
    }

    /**
     * Проверить, что при обновлении без product_ids валидация не проходит
     *
     * @return void
     */
    public function test_update_requires_product_ids()
    {
        $products = Product::factory()->count(3)->create();
        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            $products->mapWithKeys(fn ($product) => [$product->id => ['quantity' => 100]])
        );

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => 'Test Recipe',
            ],
        );

        $response->assertSessionHasErrors('products');
    }

    /**
     * Проверить, что при обновлении рецепта quantity в pivot-таблице
     * product_recipe меняется на новое значение
     *
     * @return void
     */
    public function test_update_syncs_product_quantity()
    {
        $products = Product::factory()->count(3)->create();
        $otherProducts = Product::factory()->count(2)->create();
        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            $products->mapWithKeys(fn ($product) => [$product->id => ['quantity' => 1]])
        );

        foreach ($products as $product) {
            $this->assertDatabaseHas('product_recipe', [
                'product_id' => $product->id,
                'recipe_id' => $recipe->id,
                'quantity' => 1,
            ]);
        }

        $otherProductsData = $otherProducts->map(fn ($otherProduct) => [
            'product_id' => $otherProduct->id,
            'quantity' => 2,
            'title' => $otherProduct->title,
            'unit' => $otherProduct->unit,
        ]);

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'products' => json_encode($otherProductsData),
                'servings' => $recipe->servings,
            ],
        );

        $response->assertRedirect(route('recipes.index'));

        foreach ($otherProducts as $otherProduct) {
            $this->assertDatabaseHas('product_recipe', [
                'product_id' => $otherProduct->id,
                'recipe_id' => $recipe->id,
                'quantity' => 2,
            ]);
        }
    }

    /**
     * Проверить, что при обновлении без quantity у продукта
     * валидация не проходит
     *
     * @return void
     */
    public function test_update_requires_product_quantity()
    {
        $products = Product::factory()->count(3)->create();
        $otherProducts = Product::factory()->count(2)->create();
        $recipe = Recipe::factory()->create();
        $recipe->products()->attach(
            $products->mapWithKeys(fn ($product) => [$product->id => ['quantity' => 1]])
        );

        $otherProductsData = $otherProducts->map(fn ($otherProduct) => [
            'product_id' => $otherProduct->id,
            'title' => $otherProduct->title,
            'unit' => $otherProduct->unit,
        ]);

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'products' => json_encode($otherProductsData),
                'servings' => $recipe->servings,
            ],
        );

        $response->assertSessionHasErrors('products.0.quantity');
        $response->assertSessionHasErrors('products.1.quantity');

        $messages = session('errors')->get('products.0.quantity');

        $this->assertTrue(
            collect($messages)->contains(fn ($message) => str_contains($message, 'Введите количество')),
        );
    }

    /**
     * Проверить, что рецепт удаляется
     *
     * @return void
     */
    public function test_destroy_deletes_recipe()
    {
        $products = Product::factory()->count(3)->create();

        $recipe = Recipe::factory()
            ->hasAttached($products)
            ->create();

        $response = $this->delete(route('recipes.destroy', $recipe));

        $response->assertRedirect(route('recipes.index'));

        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    /**
     * Убедиться, что удаление рецепта с привязанными продуктами не падает
     * с ошибкой внешнего ключа и очищает связи в product_recipe
     * (cascadeOnDelete в миграции)
     *
     * @return void
     */
    public function test_destroy_recipe_with_attached_products()
    {
        $products = Product::factory()->count(3)->create();

        $recipe = Recipe::factory()
            ->hasAttached($products)
            ->create();

        $response = $this->delete(route('recipes.destroy', $recipe));
        $response->assertRedirect(route('recipes.index'));

        foreach ($products as $product) {
            $this->assertDatabaseMissing('product_recipe', ['product_id' => $product->id, 'recipe_id' => $recipe->id]);
        }
    }

    /**
     * Проверить, что `servings` сохраняется в `recipes` при создании
     * рецепта
     *
     * @return void
     */
    public function test_store_saves_recipe_servings()
    {
        // создать продукты
        $products = Product::factory()->count(3)->create();
        // конвертировать в данные
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
            'quantity' => 1,
        ]);

        // отправить запрос на создание рецепта
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 2,
            ],
        );

        // убедиться, что все ок и произошел редирект
        $response->assertRedirect(route('recipes.index'));

        // убедиться, что `servings` сохраняется в `recipes`
        $this->assertDatabaseHas('recipes', [
            'title' => 'Test Recipe',
            'servings' => 2,
        ]);
    }

    /**
     * Проверить, что без `servings` запрос на создание рецепта
     * отклоняется валидацией
     *
     * @return void
     */
    public function test_store_requires_servings()
    {
        // создать продукты
        $products = Product::factory()->count(3)->create();
        // конвертировать в данные
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
            'quantity' => 1,
        ]);

        // отправить запрос на создание рецепта, но без servings
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
            ],
        );

        // убедиться, что есть ошибка
        $response->assertSessionHasErrors('servings');
        // и что строка не попала в БД
        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Проверить, что `servings < 1` отклоняется валидацией при
     * создании рецепта
     *
     * @return void
     */
    public function test_store_rejects_servings_below_min()
    {
        // создать продукты
        $products = Product::factory()->count(3)->create();
        // конвертировать в данные
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
            'quantity' => 1,
        ]);

        // отправить запрос на создание рецепта
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 0,
            ],
        );

        // убедиться, что есть ошибка
        $response->assertSessionHasErrors('servings');
        // и что строка не попала в БД
        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Проверить, что `servings`, превышающий максимум (255),
     * отклоняется валидацией при создании рецепта
     *
     * @return void
     */
    public function test_store_rejects_servings_above_max()
    {
        // создать продукты
        $products = Product::factory()->count(3)->create();
        // конвертировать в данные
        $productsData = collect($products)->map(fn ($product) => [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'title' => $product->title,
            'quantity' => 1,
        ]);

        // отправить запрос на создание рецепта
        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'products' => json_encode($productsData),
                'servings' => 256,
            ],
        );

        // убедиться, что есть ошибка
        $response->assertSessionHasErrors('servings');
        // и что строка не попала в БД
        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Проверить, что `servings` можно изменить при обновлении
     * рецепта
     *
     * @return void
     */
    public function test_update_saves_recipe_servings()
    {
        // продукт
        $product = Product::factory()->create();
        $productData = [
            'product_id' => $product->id,
            'unit' => $product->unit->value,
            'quantity' => 10,
            'title' => $product->title,
        ];
        // рецепт
        $recipe = Recipe::factory()->create(['servings' => 4]);
        // прикрепить продукт к рецепту
        $recipe->products()->attach([
            $product->id => ['quantity' => 10],
        ]);

        // проверить, что servings был 4
        $this->assertDatabaseHas('recipes', [
            'title' => $recipe->title,
            'servings' => 4,
        ]);

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'products' => json_encode([$productData]),
                'servings' => 5,
            ]
        );

        // убедиться, что все ок и произошел редирект
        $response->assertRedirect(route('recipes.index'));
        // проверить, что servings изменился
        $this->assertDatabaseHas('recipes', [
            'title' => $recipe->title,
            'servings' => 5,
        ]);
    }
}
