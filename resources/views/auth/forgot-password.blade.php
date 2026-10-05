<x-layouts.app>
    <x-slot:title>Забыли пароль</x-slot:title>

    <form
        method="POST"
        class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-md p-8"
        action="{{route('password.email')}}"
    >
        @csrf
        <h1 class="mb-2 text-2xl font-bold">Забыли пароль</h1>
        <p class="text-sm/normal text-gray-500 mb-4">Укажите почту - пришлём ссылку для установки нового пароля.</p>

        @if(session('status'))
            <p class="alert-success my-6">{{ session('status') }}</p>
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

        <button class="w-full btn-primary mb-6" type="submit">Отправить ссылку</button>

        <p class="text-sm text-gray-500 text-center">
            Вспомнили пароль?
            <a
                href="{{route('login')}}"
                class="decoration-0 text-blue-600 hover:text-blue-500 transition-colors duration-150"
            >Вернуться ко входу</a>
        </p>

    </form>
</x-layouts.app>
