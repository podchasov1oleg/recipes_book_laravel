<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты функционала продуктов
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверить, что мы видим продукты
     *
     * @return void
     */
    public function test_index_displays_products()
    {
        $models = Product::factory()->count(3)->create();

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);

        foreach ($models as $model) {
            $response->assertSee($model->title);
        }
    }

    /**
     * Убедиться в том, что мы видим инпут формы создания товара
     *
     * @return void
     */
    public function test_create_displays_form()
    {
        $response = $this->get(route('products.create'));

        $response->assertStatus(200);

        $response->assertSee('name="title"', false);
    }

    /**
     * Убедиться в том, что post запрос создал продукт
     *
     * @return void
     */
    public function test_store_creates_product()
    {
        $response = $this->post(route('products.store'), ['title' => 'Test Product']);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['title' => 'Test Product']);
    }

    /**
     * Убедиться в том, что нельзя создать продукт без названия
     *
     * @return void
     */
    public function test_store_requires_title()
    {
        $response = $this->post(route('products.store'), ['title' => '']);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('products', ['title' => '']);
    }

    /**
     * Убедитсья в работе валидации 256 символов на название продукта
     *
     * @return void
     */
    public function test_store_title_max_length()
    {
        $response = $this->post(route('products.store'), ['title' => str_repeat('a', 256)]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('products', ['title' => str_repeat('a', 256)]);
    }

    /**
     * Убедиться в том, что названия у продуктов уникальные
     *
     * @return void
     */
    public function test_store_title_must_be_unique()
    {
        $product = Product::factory()->create();

        $response = $this->post(route('products.store'), ['title' => $product->title]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseCount('products', 1);
    }

    /**
     * Убедиться, что форма редактирования отображается с данными продукта
     *
     * @return void
     */
    public function test_edit_displays_form_with_existing_data()
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.edit', $product));

        $response->assertStatus(200);

        $response->assertSee($product->title);
    }

    /**
     * Убедиться в том, что форма изменения продукта меняет его название
     *
     * @return void
     */
    public function test_update_changes_title()
    {
        $product = Product::factory()->create();

        $response = $this->put(route('products.update', $product), ['title' => 'Test Product']);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['title' => 'Test Product']);
    }

    /**
     * Убедиться, что сохранение продукта с тем же именем не вызовет ошибку
     *
     * @return void
     */
    public function test_update_without_changing_title_does_not_fail()
    {
        $product = Product::factory()->create();

        $response = $this->put(route('products.update', $product), ['title' => $product->title]);

        $response->assertRedirect(route('products.index'));
    }

    /**
     * Убедиться, что нельзя назвать 2 продукта одним именем
     *
     * @return void
     */
    public function test_update_title_conflicts_with_another_product()
    {
        $product1 = Product::factory()->create();

        $product2 = Product::factory()->create();

        $response = $this->put(route('products.update', $product1), ['title' => $product2->title]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseHas('products', ['title' => $product1->title]);

        $this->assertDatabaseHas('products', ['title' => $product2->title]);
    }

    /**
     * Убедиться в том, что удаление продукта отражается в БД
     *
     * @return void
     */
    public function test_destroy_deletes_product()
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['title' => $product->title]);
    }
}
