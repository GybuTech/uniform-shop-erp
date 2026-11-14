<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" autocomplete="off">
        @csrf

        {{-- Dummy inputs to intercept and defeat aggressive browser password managers --}}
        <input type="text" style="position:fixed; opacity:0; pointer-events:none; left:-9999px;" tabindex="-1" name="fake_username_trap" autocomplete="off" />
        <input type="password" style="position:fixed; opacity:0; pointer-events:none; left:-9999px;" tabindex="-1" name="fake_password_trap" autocomplete="off" />

        <!-- Username -->
        <div>
            <x-input-label for="login" :value="__('Username')" />
            <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="off" readonly onfocus="this.removeAttribute('readonly')" placeholder="Enter your username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')"
                            placeholder="Enter your password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
