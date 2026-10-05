<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthUserRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Контроллер авторизации пользователя
 */
class LoginUserController extends Controller
{
    /**
     * Отобразить форму авторизации
     *
     * @return Factory|\Illuminate\Contracts\View\View|View
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Обработать запрос на авторизацию пользователя
     *
     * @param AuthUserRequest $request
     * @return RedirectResponse
     */
    public function auth(AuthUserRequest $request)
    {
        $validated = $request->validated();

        if (
            Auth::attempt(
                [
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ],
                (bool) ($validated['remember_me'] ?? false))
        ) {
            $request->session()->regenerate();

            return redirect()->intended();
        }

        throw ValidationException::withMessages(['email' => trans('auth.failed')]);
    }

    /**
     * Обработать запрос на выход пользователя
     *
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
