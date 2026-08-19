<?php

namespace Tests\Feature;

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

        $response->assertSee('name="product_ids[]', false);

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
            ['title' => 'Test Recipe', 'product_ids' => [$product->id]],
        );

        $response->assertRedirect(route('recipes.index'));

        $recipe = Recipe::where('title', 'Test Recipe')->firstOrFail();

        $this->assertDatabaseHas('recipes', ['title' => 'Test Recipe']);

        $this->assertDatabaseHas('product_recipe', ['product_id' => $product->id, 'recipe_id' => $recipe->id]);
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

        $response = $this->post(
            route('recipes.store'),
            [
                'title' => 'Test Recipe',
                'product_ids' => $products->pluck('id')->all(),
            ],
        );

        $response->assertRedirect(route('recipes.index'));

        $recipe = Recipe::where('title', 'Test Recipe')->firstOrFail();

        foreach ($products as $product) {
            $this->assertDatabaseHas('product_recipe', ['product_id' => $product->id, 'recipe_id' => $recipe->id]);
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
            ],
        );

        $response->assertSessionHasErrors('product_ids');

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * Убедиться, что пустой массив product_ids отклоняется правилом min:1,
     * а не просто required
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
                'product_ids' => [],
            ],
        );

        $response->assertSessionHasErrors('product_ids');

        $messages = session('errors')->get('product_ids');

        $this->assertTrue(
            collect($messages)->contains(fn ($message) => str_contains($message, 'обязательно для заполнения')),
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
                'product_ids' => [1],
            ],
        );

        $response->assertSessionHasErrors('product_ids.0');

        $messages = session('errors')->get('product_ids.0');

        $this->assertTrue(
            collect($messages)->contains(
                fn ($message) => str_contains($message, 'Выбранное значение поля')
                    && str_contains($message, 'недопустимо'),
            ),
        );
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

        $recipe = Recipe::factory()
            ->hasAttached($products)
            ->create();

        $response = $this->get(route('recipes.edit', $recipe));

        $content = $response->getContent();

        foreach ($products as $product) {
            preg_match('/<option\s+value="'.$product->id.'"[^>]*>/', $content, $matches);

            $this->assertNotEmpty($matches);
            $this->assertStringContainsString('selected', $matches[0]);
        }

        preg_match('/<option\s+value="'.$otherProduct->id.'"[^>]*>/', $content, $matches);

        $this->assertStringNotContainsString('selected', $matches[0]);
    }

    /**
     * Проверить, что название рецепта успешно изменяется
     *
     * @return void
     */
    public function test_update_changes_title()
    {
        $products = Product::factory()->count(1)->create();

        $recipe = Recipe::factory()
            ->hasAttached(Product::inRandomOrder()->take(1)->get())
            ->create();

        $oldTitle = $recipe->title;

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => 'Test Recipe',
                'product_ids' => $products->pluck('id')->all(),
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

        $recipe = Recipe::factory()
            ->hasAttached($products)
            ->create();

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'product_ids' => $products->pluck('id')->all(),
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

        $recipe = Recipe::factory()
            ->hasAttached($products)
            ->create();

        foreach ($products as $product) {
            $this->assertDatabaseHas('product_recipe', ['product_id' => $product->id, 'recipe_id' => $recipe->id]);
        }

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => $recipe->title,
                'product_ids' => $otherProducts->pluck('id')->all(),
            ]
        );

        $response->assertRedirect(route('recipes.index'));

        foreach ($otherProducts as $product) {
            $this->assertDatabaseHas('product_recipe', ['product_id' => $product->id, 'recipe_id' => $recipe->id]);
        }

        foreach ($products as $product) {
            $this->assertDatabaseMissing('product_recipe', ['product_id' => $product->id, 'recipe_id' => $recipe->id]);
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

        $recipe = Recipe::factory()
            ->hasAttached($products)
            ->create();

        $response = $this->put(
            route('recipes.update', $recipe),
            [
                'title' => 'Test Recipe',
            ],
        );

        $response->assertSessionHasErrors('product_ids');
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
}
