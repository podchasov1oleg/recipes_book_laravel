<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Контроллер для обработки запросов забытого пароля
 */
class PasswordResetController extends Controller
{
    /**
     * Отобразить форму забытого пароля
     *
     * @return Factory|\Illuminate\Contracts\View\View|View
     */
    public function index()
    {
        return view('auth.forgot-password');
    }

    /**
     * Обработать запрос отправленной формы восстановления пароля
     *
     * @return RedirectResponse
     */
    public function email(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::ResetLinkSent
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Отобразить форму нового пароля
     *
     * @param Request $request
     * @param string $token
     * @return Factory|\Illuminate\Contracts\View\View|View
     */
    public function reset(Request $request, string $token)
    {
        $email = $request->query('email');

        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
    }

    /**
     * Обработать запрос сохранения нового пароля для пользователя
     *
     * @param UpdatePasswordRequest $request
     * @return RedirectResponse
     */
    public function update(UpdatePasswordRequest $request)
    {
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                    ->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
}
