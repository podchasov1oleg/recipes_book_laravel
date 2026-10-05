<?php

namespace Tests;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Базовый тест кейс
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Пользователь для теста с домохозяйством
     *
     * @var User
     */
    protected User $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // создать пользователя и домохозяйство
        $this->user = User::factory()->create();
        Household::factory()->for($this->user)->create();
    }
}
