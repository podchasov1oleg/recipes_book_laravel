<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Тесты функционала сброса пароля
 */
class PasswordResetTest extends TestCase
{
    /**
     * Убедиться, что гость видит форму "забыли пароль"
     *
     * @return void
     */
    public function test_forgot_password_displays_form()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);

        $response->assertViewIs('auth.forgot-password');
    }

    /**
     * Убедиться, что для существующей почты отправляется уведомление `ResetPassword`
     *
     * @return void
     */
    public function test_forgot_password_sends_reset_link()
    {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertRedirectBack();

        $response->assertSessionHas('status');

        Notification::assertSentTo($this->user, ResetPassword::class);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $this->user->email]);
    }

    /**
     * Убедиться, что для неизвестной почты возвращается ошибка
     *
     * @return void
     */
    public function test_forgot_password_shows_error_for_unknown_email()
    {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'random@mail.ru',
        ]);

        $response->assertRedirectBackWithErrors('email');

        Notification::assertNothingSent();

        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    /**
     * Убедиться, что форма нового пароля открывается по токену, почта подставлена из query.
     *
     * @return void
     */
    public function test_reset_password_displays_form_with_email()
    {
        $token = Password::createToken($this->user);
        $url = route('password.reset', [
            'token' => $token,
            'email' => $this->user->email,
        ]);
        $response = $this->get($url);

        $response->assertStatus(200);

        $response->assertViewIs('auth.reset-password');

        $response->assertSee($this->user->email);

        $response->assertSee($token);
    }

    /**
     * Убедиться, что с валидным токеном пароль меняется, после чего можно войти с новым паролем
     *
     * @return void
     */
    public function test_reset_password_changes_password()
    {
        $token = Password::createToken($this->user);
        $newPassword = 'new-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHas('status');

        $loginResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => $newPassword,
        ]);

        $loginResponse->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Убедиться, что с подменённым токеном пароль не меняется, ошибка на `email`
     *
     * @return void
     */
    public function test_reset_password_rejects_invalid_token()
    {
        $initialPassword = $this->user->password;
        $token = 'random-string';
        $newPassword = 'new-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirectBackWithErrors('email');

        $this->assertEquals($initialPassword, $this->user->fresh()->password);
    }

    /**
     * Убедиться, что токен одного пользователя не подходит для почты другого
     *
     * @return void
     */
    public function test_reset_password_rejects_token_of_other_user()
    {
        $initialPassword = $this->user->password;
        $otherUser = User::factory()->create();

        $token = Password::createToken($otherUser);
        $newPassword = 'new-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirectBackWithErrors('email');

        $this->assertEquals($initialPassword, $this->user->fresh()->password);
    }

    /**
     * Убедиться, что после успешного сброса тот же токен уже не работает
     *
     * @return void
     */
    public function test_reset_password_token_cannot_be_reused()
    {
        $token = Password::createToken($this->user);
        $newPassword = 'new-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect('/login');

        $repeatedResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $repeatedResponse->assertRedirectBackWithErrors('email');
    }

    /**
     * Убедиться, что новый пароль без подтверждения отклоняется.
     *
     * @return void
     */
    public function test_reset_password_requires_confirmation()
    {
        $initialPassword = $this->user->password;

        $token = Password::createToken($this->user);
        $newPassword = 'new-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
        ]);

        $response->assertRedirectBackWithErrors('password');

        $this->assertEquals($initialPassword, $this->user->fresh()->password);
    }
}
