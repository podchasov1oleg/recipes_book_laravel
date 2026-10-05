<x-layouts.app>
    <x-slot:title>Регистрация нового пользователя</x-slot:title>

    <form
        action="{{route('register.store')}}"
        class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-md p-8"
        method="POST"
    >
        @csrf
        <h1 class="mb-2 text-2xl font-bold">Регистрация</h1>
        <p class="text-sm text-gray-500 mb-6">
            Заведём аккаунт и ваше домохозяйство — рецепты и меню будут храниться в нём.
        </p>

        {{--Поле имя--}}
        <label class="text-sm mb-1 block text-gray-700" for="name">Имя:</label>
        <input
            @class([
                'block',
                'text-input',
                'mb-4' => !$errors->has('name'),
                'is-invalid' => $errors->has('name'),
            ])
            id="name"
            type="text"
            name="name"
            value="{{old('name')}}"
            placeholder="Как к вам обращаться"
        />
        @error('name')
            <p class="text-red-500 text-sm mb-4">{{$message}}</p>
        @enderror

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

        {{--Название домохозяйства--}}
        <label class="text-sm mb-1 block text-gray-700" for="household_name">Название домохозяйства:</label>
        <input
            @class([
                'block',
                'text-input',
                'is-invalid' => $errors->has('household_name'),
            ])
            id="household_name"
            type="text"
            name="household_name"
            value="{{old('household_name')}}"
            placeholder="Например, «Наша кухня»"
        />
        <p
            @class([
                'text-xs',
                'text-gray-500',
                'mb-4' => !$errors->has('household_name'),
            ])
        >Можно оставить пустым — назовём «Кухня {имя}».</p>
        @error('household_name')
            <p class="text-red-500 text-sm mb-4">{{$message}}</p>
        @enderror

        {{--Поле пароля--}}
        <label class="text-sm mb-1 block text-gray-700" for="password">Пароль:</label>
        <input
            @class([
                'block',
                'text-input',
                'is-invalid' => $errors->has('password'),
            ])
            id="password"
            type="password"
            name="password"
        />
        <p
            @class([
                'text-xs',
                'text-gray-500',
                'mb-4' => !$errors->has('password'),
            ])
        >Минимум 8 символов.</p>
        @error('password')
            <p class="text-red-500 text-sm mb-4">{{$message}}</p>
        @enderror

        {{--Поле повтора пароля--}}
        <label class="text-sm mb-1 block text-gray-700" for="password_confirmation">Повторите пароль:</label>
        <input
            @class([
                'block',
                'text-input',
                'is-invalid' => $errors->has('password_confirmation'),
                'mb-4' => !$errors->has('password_confirmation'),
            ])
            id="password_confirmation"
            type="password"
            name="password_confirmation"
        />
        @error('password_confirmation')
            <p class="text-red-500 text-sm mb-4">{{$message}}</p>
        @enderror

        <button class="w-full btn-primary mb-6" type="submit">Зарегистрироваться</button>

        <p class="text-sm text-gray-500 text-center">
            Уже есть акканут?
            <a
                href="{{route('login')}}"
                class="decoration-0 text-blue-600 hover:text-blue-500 transition-colors duration-150"
            >Войти</a>
        </p>
    </form>
</x-layouts.app>
