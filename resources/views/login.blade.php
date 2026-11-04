@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Log in to your account')" data-auth-page="login">
        <div class="flex flex-col gap-4">
            @include('auth::partials.status')

            <x-forms::form :action="$urls['login.store']" class="flex flex-col gap-4" data-login-form>
                <x-forms::email
                    :name="$auth_type['username']"
                    :label="__('Email address')"
                    required
                    autofocus
                    autocomplete="username webauthn"
                    dir="ltr" />

                <x-forms::password
                    name="password"
                    :label="__('Password')"
                    required
                    autocomplete="current-password"
                    dir="ltr" />

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <x-forms::checkbox name="remember" :label="__('Remember me')" value="1" :model="false" />

                    @if($can_reset_password)
                        <a href="{{ $urls['password.request'] }}" class="text-sm underline">{{ __('Forgot your password?') }}</a>
                    @endif
                </div>

                <x-forms::submit class="w-full">{{ __('Log in') }}</x-forms::submit>
            </x-forms::form>

            @if($can_use_passkeys)
                @include('auth::partials.passkey-login')
            @endif

            @foreach($auth_type['login_methods'] as $method)
                <x-forms::link-button :url="$method['url']" color="secondary" class="w-full" data-login-method="{{ $method['key'] }}">
                    {{ $method['label'] }}
                </x-forms::link-button>
            @endforeach

            @if($can_register)
                <p class="text-center text-sm">
                    {{ __("Don't have an account?") }}
                    <a href="{{ $urls['register'] }}" class="underline">{{ __('Sign up') }}</a>
                </p>
            @endif
        </div>
    </x-forms::card>
@endsection
