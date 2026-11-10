@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Create an account')" data-auth-page="register">
        <x-forms::form :action="$urls['register.store']" class="flex flex-col gap-4" summary>
            <x-forms::text name="name" :label="__('Name')" required autofocus autocomplete="name" maxlength="255" dir="auto" />

            <x-forms::email :name="$auth_type['username']" :label="__('Email address')" required autocomplete="email" dir="ltr" />

            <x-forms::password name="password" :label="__('Password')" required autocomplete="new-password" dir="ltr" />

            <x-forms::password name="password_confirmation" :label="__('Confirm password')" required autocomplete="new-password" dir="ltr" />

            <x-forms::submit class="w-full">{{ __('Create account') }}</x-forms::submit>

            <p class="text-center text-sm">
                {{ __('Already have an account?') }}
                <a href="{{ $urls['login'] }}" class="underline">{{ __('Log in') }}</a>
            </p>
        </x-forms::form>
    </x-forms::card>
@endsection
