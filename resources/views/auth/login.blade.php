<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login Type Selection -->
    <div class="mb-6 text-center">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <a href="{{ route('login') }}" 
               class="px-4 py-2 text-sm font-medium {{ !request('as') || request('as') !== 'owner' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-emerald-50' }} rounded-l-lg border border-gray-200">
                Login sebagai Customer
            </a>
            <a href="{{ route('login', ['as' => 'owner']) }}" 
               class="px-4 py-2 text-sm font-medium {{ request('as') === 'owner' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-emerald-50' }} rounded-r-lg border border-gray-200">
                Login sebagai Owner
            </a>
        </div>
    </div>

    @if(request('as') === 'owner')
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-md">
            <p class="text-sm text-emerald-800">
                <strong>Login sebagai Owner:</strong> Hanya akun dengan role Owner yang dapat login melalui halaman ini.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        @if(request('as') === 'owner')
            <input type="hidden" name="login_as" value="owner">
        @endif

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:ring-emerald-500">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
