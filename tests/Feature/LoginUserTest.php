<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Тесты функционала авторизации пользователя
 */
class LoginUserTest extends TestCase
{
    /**
     * Убедиться, что гость видит форму входа
     *
     * @return void
     */
    public function test_login_displays_form()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        $response->assertViewIs('auth.login');
    }

    /**
     * Убедиться, что с верными данными пользователь входит и попадает на intended страницу
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function test_login_authenticates_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);

        $response->assertRedirect(route('home'));

        $response->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Убедиться, что с неверным паролем входа не происходит, ошибка висит на поле email
     *
     * @return void
     */
    public function test_login_fails_with_wrong_password()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirectBack();

        $response->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    /**
     * Логин по несуществующей почте дает ошибку тоже по полю email
     *
     * @return void
     */
    public function test_login_fails_with_unknown_email()
    {
        $response = $this->post('/login', [
            'email' => 'wrong-email@mail.ru',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirectBack();

        $response->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    /**
     * Убедиться, что если гость открыл закрытую страницу, вошёл, то вернулся именно на неё
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function test_login_redirects_to_intended_page()
    {
        $response = $this->get('/recipes');

        $response->assertRedirect('/login');

        $otherResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $otherResponse->assertRedirect('/recipes');

        $otherResponse->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Убедиться, что после 6 попыток за минуту следующая получает 429
     *
     * @return void
     */
    public function test_login_is_throttled_after_too_many_attempts()
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => $this->user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(429);

        $this->assertGuest();
    }

    /**
     * Убедиться, что при заполнении поля "запомнить меня" у пользователя проставляется remember_token
     *
     * @return void
     */
    public function test_login_remember_me_sets_remember_token()
    {
        $user = User::factory()->create([
            'email' => 'foo@bar.ru',
            'password' => 'password',
            'remember_token' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember_me' => 1,
        ]);

        $response->assertStatus(302);

        $this->assertAuthenticatedAs($user);

        $this->assertNotNull($user->fresh()->remember_token);

        $response->assertCookie(Auth::guard()->getRecallerName());
    }

    /**
     * Убедиться, что после выхода пользователь - снова гость
     *
     * @return void
     */
    public function test_logout_ends_session()
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertStatus(302);

        $this->assertGuest();

        $response = $this->get('/recipes');

        $response->assertRedirect('/login');
    }
}
