<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Household;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Контроллер регистрации пользователей
 */
class RegisterUserController extends Controller
{
    /**
     * Открыть форму регистрации пользователей
     *
     * @return Factory|\Illuminate\View\View|View
     */
    public function index()
    {
        return view('auth.register');
    }

    /**
     * Обработать запрос на регистрацию пользователя
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreUserRequest $request)
    {
        $user = DB::transaction(function() use ($request) {
            // получить провалидированные данные из запроса
            $validated = $request->validated();

            // создать пользователя
            $user = new User($validated);
            $user->save();

            // создать домохоз-во
            $householdName = empty($validated['household_name'])
                ? 'Кухня ' . $user->name
                : $validated['household_name'];
            $household = new Household(['name' => $householdName]);
            $household->user()->associate($user)->save();

            return $user;
        });

        // авторизовать пользователя
        Auth::login($user);

        // отправить событие о регистрации пользователя
        event(new Registered($user));

        return redirect()->route('home');
    }
}
