<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Отобразить форму подтверждения почты
     *
     * @return Factory|\Illuminate\Contracts\View\View|View|RedirectResponse
     */
    public function notice()
    {
        // пользователя с подтвержденной почтой редиректить на главную
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->to(route('home'));
        }

        return view('auth.verify-email');
    }

    /**
     * Верифицировать почту пользователя
     *
     * @param EmailVerificationRequest $request
     * @return RedirectResponse|Redirector
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect(route('home'))->with('message', 'Почта успешно подтверждена!');
    }

    /**
     * Обработать запрос на повторную отправку пользователю ссылки на подтверждение почты
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function send(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Ссылка на подтверждение отправлена!');
    }
}
