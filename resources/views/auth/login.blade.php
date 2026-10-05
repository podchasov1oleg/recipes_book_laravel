<x-layouts.app>
    <x-slot:title>Войдите</x-slot:title>

    <form
        method="POST"
        class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-md p-8"
        action="{{route('login.auth')}}"
    >
        @csrf
        <h1 class="mb-2 text-2xl font-bold">Вход</h1>
        <p class="text-sm text-gray-500 mb-6">Войдите, чтобы планировать меню и список покупок.</p>

        {{--flash message--}}
        @if(session('status'))
            <p class="alert-success mb-6">{{ session('status') }}</p>
        @endif

        {{--Поле почты--}}
        <label class="text-sm mb-1 block text-gray-700" for="email">Email:</label>
        <input
            @class([
                'block',
                'text-input',
                'mb-4' => !$errors->has('email'),
                'is-invalid' => $errors->has('email'),
            ])
            id="email"
            type="email"
            name="email"
            value="{{old('email')}}"
            placeholder="mail@example.com"
        />
        @error('email')
            <p class="text-red-500 text-sm mb-4">{{$message}}</p>
        @enderror

        {{--Поле пароля--}}
        <div class="flex justify-between items-center">
            <label class="text-sm mb-1 block text-gray-700" for="password">Пароль:</label>
            <a
                class="text-sm decoration-0 text-blue-600 hover:text-blue-500 transition-colors duration-150"
                href="{{route('password.request')}}"
                target="_blank"
            >Забыли пароль?</a>
        </div>
        <input
            @class([
                'block',
                'text-input',
                'is-invalid' => $errors->has('password'),
                'mb-4' => !$errors->has('password'),
            ])
            id="password"
            type="password"
            name="password"
        />
        @error('password')
            <p class="text-red-500 text-sm mb-4">{{$message}}</p>
        @enderror

        <div class="mb-6 flex items-center gap-2">
            <input class="w-4 h-4" id="remember_me" type="checkbox" name="remember_me" value="1" @checked(old('remember_me'))/>
            <label class="text-sm text-gray-700" for="remember_me">Запомнить меня</label>
        </div>

        <button class="w-full btn-primary mb-6" type="submit">Войти</button>

        <p class="text-sm text-gray-500 text-center">
            Нет аккаунта?
            <a
                href="{{route('register')}}"
                class="decoration-0 text-blue-600 hover:text-blue-500 transition-colors duration-150"
            >Зарегистрироваться</a>
        </p>
    </form>
</x-layouts.app>
