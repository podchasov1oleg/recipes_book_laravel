<x-layouts.app>
    <x-slot:title>Новый пароль</x-slot:title>

    <form
        method="POST"
        class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-md p-8"
        action="{{route('password.update')}}"
    >
        @csrf
        <input type="hidden" name="token" value="{{$token}}">
        <h1 class="mb-2 text-2xl font-bold">Новый пароль</h1>
        <p class="text-sm/normal text-gray-500 mb-6">
            Придумайте пароль для аккаунта <b class="font-bold">{{$email}}</b>.
        </p>

        {{--Поле почты--}}
        <label class="text-sm mb-1 block text-gray-700" for="email">Email:</label>
        <input
            class="block text-input mb-4"
            id="email"
            type="email"
            name="email"
            value="{{$email}}"
            readonly
        />

        {{--Поле пароля--}}
        <label class="text-sm mb-1 block text-gray-700" for="password">Новый пароль:</label>
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

        <button class="w-full btn-primary" type="submit">Сохранить пароль</button>

        @error('email')
            <p class="alert-error my-6">
                {{$message}}
                <a class="text-red-600 underline" href="{{ route('password.request') }}">Запросить новую ссылку.</a>
            </p>
        @enderror
    </form>
</x-layouts.app>
