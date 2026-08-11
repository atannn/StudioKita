<x-guest-layout>
    <!-- Register Type Selection -->
    <div class="mb-6 text-center">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <a href="{{ route('register') }}"
               class="px-4 py-2 text-sm font-medium {{ !request('as') || request('as') !== 'owner' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-emerald-50' }} rounded-l-lg border border-gray-200">
                Register sebagai Customer
            </a>
            <a href="{{ route('register', ['as' => 'owner']) }}"
               class="px-4 py-2 text-sm font-medium {{ request('as') === 'owner' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-emerald-50' }} rounded-r-lg border border-gray-200">
                Register sebagai Owner
            </a>
        </div>
    </div>

    @if(request('as') === 'owner')
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-md">
            <p class="text-sm text-emerald-800">
                <strong>Register sebagai Owner:</strong> Lengkapi akun untuk mengelola layanan, jadwal, dan booking studio.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="role" value="{{ request('as') === 'owner' ? 'owner' : 'customer' }}">

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4 bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:ring-emerald-500">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
