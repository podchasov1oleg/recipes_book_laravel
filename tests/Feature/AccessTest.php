<?php

namespace Tests\Feature;

use App\Models\MenuDay;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Тесты доступа к страницам
 */
class AccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Убедиться, что главная открывается без входа
     *
     * @return void
     */
    public function test_home_is_public()
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertViewIs('index');
    }

    /**
     * Закрытые маршруты приложения
     *
     * @return array<string, array{string, string}>
     */
    public static function closedRoutesProvider(): array
    {
        return [
            'список продуктов' => ['get', '/products'],
            'форма создания продукта' => ['get', '/products/create'],
            'создание продукта' => ['post', '/products'],
            'форма редактирования продукта' => ['get', '/products/{product}/edit'],
            'изменение продукта' => ['put', '/products/{product}'],
            'удаление продукта' => ['delete', '/products/{product}'],
            'список рецептов' => ['get', '/recipes'],
            'форма создания рецепта' => ['get', '/recipes/create'],
            'создание рецепта' => ['post', '/recipes'],
            'форма редактирования рецепта' => ['get', '/recipes/{recipe}/edit'],
            'изменение рецепта' => ['put', '/recipes/{recipe}'],
            'удаление рецепта' => ['delete', '/recipes/{recipe}'],
            'меню на неделю' => ['get', '/week-menu'],
            'добавление рецептов в день' => ['post', '/week-menu'],
            'изменение порций рецепта в дне' => ['patch', '/week-menu/{menuDay}/{recipe}'],
            'удаление рецепта из дня' => ['delete', '/week-menu/{menuDay}/{recipe}'],
            'список покупок' => ['get', '/week-menu/shopping-list'],
            'страница подтверждения почты' => ['get', '/email/verify'],
            'выход' => ['post', '/logout'],
        ];
    }

    /**
     * Убедиться, что гость с закрытых маршрутов уходит на форму входа
     *
     * @return void
     */
    #[DataProvider('closedRoutesProvider')]
    public function test_guest_is_redirected_to_login(string $method, string $url)
    {
        $response = $this->call($method, $this->substituteRouteParameters($url));

        $response->assertRedirect('/login');
    }

    /**
     * Подставить в маршрут идентификаторы существующих записей
     */
    private function substituteRouteParameters(string $url): string
    {
        $replacements = [];

        if (str_contains($url, '{product}')) {
            $replacements['{product}'] = Product::factory()->create()->id;
        }

        if (str_contains($url, '{recipe}')) {
            $replacements['{recipe}'] = Recipe::factory()->create()->id;
        }

        if (str_contains($url, '{menuDay}')) {
            $replacements['{menuDay}'] = MenuDay::factory()->create()->id;
        }

        return strtr($url, $replacements);
    }
}
