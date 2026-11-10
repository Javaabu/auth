@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Forgot your password?')" data-auth-page="forgot-password">
        <div class="flex flex-col gap-4">
            <p class="text-sm">{{ __('Enter your email address and we will email you a link to choose a new password.') }}</p>

            @include('auth::partials.status')

            <x-forms::form :action="$urls['password.email']" class="flex flex-col gap-4">
                <x-forms::email :name="$auth_type['username']" :label="__('Email address')" required autofocus autocomplete="email" dir="ltr" />

                <x-forms::submit class="w-full">{{ __('Email password reset link') }}</x-forms::submit>
            </x-forms::form>

            <p class="text-center text-sm">
                <a href="{{ $urls['login'] }}" class="underline">{{ __('Back to log in') }}</a>
            </p>
        </div>
    </x-forms::card>
@endsection
