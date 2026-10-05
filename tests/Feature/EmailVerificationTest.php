<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Тесты функционала проверки почты
 */
class EmailVerificationTest extends TestCase
{
    /**
     * Убедиться, что неподтверждённый пользователь видит страницу подтверждения почты
     *
     * @return void
     */
    public function test_verification_notice_displays_for_unverified_user()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);

        $response->assertViewIs('auth.verify-email');
    }

    /**
     * Убедиться, что неподтверждённый пользователь видит в навигации ссылку "подтвердите почту"
     *
     * @return void
     */
    public function test_nav_displays_verification_link_for_unverified_user()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertSee('Подтвердите почту');
    }

    /**
     * Убедиться, что подтверждённого пользователя со страницы уводит на главную
     *
     * @return void
     */
    public function test_verification_notice_redirects_verified_user()
    {
        $response = $this->actingAs($this->user)->get('/email/verify');

        $response->assertRedirect(route('home'));
    }

    /**
     * Убедиться, что переход по подписанной ссылке проставляет `email_verified_at`
     *
     * @return void
     */
    public function test_verification_link_verifies_email()
    {
        $user = User::factory()->unverified()->create();

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $response = $this->actingAs($user)->get($verifyUrl);

        $response->assertRedirect(route('home'));

        $response->assertSessionHas('message', 'Почта успешно подтверждена!');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /**
     * Убедиться, что ссылка с чужим хешем почты не подтверждает
     *
     * @return void
     */
    public function test_verification_link_with_invalid_hash_is_rejected()
    {
        $user = User::factory()->unverified()->create();

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($this->user->email)],
        );

        $response = $this->actingAs($user)->get($verifyUrl);

        $response->assertStatus(403);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    /**
     * Убедиться, что ссылка без подписи или с испорченной подписью даёт 403
     *
     * @return void
     */
    public function test_verification_link_without_signature_is_rejected()
    {
        $user = User::factory()->unverified()->create();

        // хеш верный, отсутствует только подпись - иначе 403 пришёл бы от проверки хеша
        $verifyUrl = route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]);

        $response = $this->actingAs($user)->get($verifyUrl);

        $response->assertStatus(403);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    /**
     * Убедиться, что повторная отправка шлет пользователю уведомление VerifyEmail
     *
     * @return void
     */
    public function test_verification_resend_sends_notification()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirectBack();

        $response->assertSessionHas('message', 'Ссылка на подтверждение отправлена!');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * Убедиться, что неподтверждённого пользователя с закрытых страниц уводит на страницу подтверждения
     *
     * @return void
     */
    public function test_unverified_user_cannot_access_recipes()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/recipes');

        $response->assertRedirect('/email/verify');
    }
}
