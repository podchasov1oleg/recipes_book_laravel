<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Тесты функционала регистрации пользователя
 */
class RegisterUserTest extends TestCase
{
    /**
     * Убедиться, что гость видит форму регистрации
     *
     * @return void
     */
    public function test_register_displays_form()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);

        $response->assertViewIs('auth.register');
    }

    /**
     * Убедиться, что отправка формы создает пользователя и домохоз-во
     *
     * @return void
     */
    public function test_register_creates_user()
    {
        $response = $this->post('/register', [
            'name' => 'username',
            'email' => 'foo@bar.ru',
            'household_name' => 'householdname',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('home'));

        $user = User::where('name', 'username')->first();

        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('users', [
            'name' => 'username',
            'email' => 'foo@bar.ru',
        ]);

        $this->assertDatabaseHas('households', [
            'name' => 'householdname',
            'user_id' => $user->id,
        ]);

        $this->assertTrue(Hash::check('password', $user->password));
    }

    /**
     * Убедиться, что без переданного названия домохоз-ва будет создано с дефолтным значением
     *
     * @return void
     */
    public function test_register_uses_default_household_name_when_empty()
    {
        $response = $this->post('/register', [
            'name' => 'username',
            'email' => 'foo@bar.ru',
            'household_name' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('home'));

        $user = User::where('name', 'username')->first();

        $this->assertDatabaseHas('households', [
            'name' => 'Кухня username',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Убедиться, что регистрация пользователя создает соответствующее событие
     *
     * @return void
     */
    public function test_register_dispatches_registered_event()
    {
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'name' => 'username',
            'email' => 'foo@bar.ru',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('home'));

        Event::assertDispatched(function (Registered $event) {
            return $event->user->email === 'foo@bar.ru';
        });
    }

    /**
     * Убедиться, что если почта уже существует, то регистрация будет отменена
     *
     * @return void
     */
    public function test_register_rejects_taken_email()
    {
        $response = $this->post('/register', [
            'name' => 'username',
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        // есть только пользователь, которого создали в setUp
        $this->assertDatabaseCount('users', 1);

        $this->assertGuest();
    }

    /**
     * Убедиться, что требуется подтверждение пароля
     *
     * @return void
     */
    public function test_register_requires_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'username',
            'email' => 'foo@bar.ru',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);

        $this->assertGuest();
    }

    /**
     * Убедиться, что пароль короче минимума в Password::defaults() будет отклонен
     *
     * @return void
     */
    public function test_register_rejects_short_password()
    {
        $response = $this->post('/register', [
            'name' => 'username',
            'email' => $this->user->email,
            'password' => '123',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);

        $this->assertGuest();
    }

    /**
     * Убедиться, что авторизованного пользователя не пустит на страницу регистрации
     *
     * @return void
     */
    public function test_register_is_not_available_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/register');

        $response->assertRedirect(route('home'));
    }
}
