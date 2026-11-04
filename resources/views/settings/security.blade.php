@extends($layout)

@section($layout_section)
    @manifestRequire('auth::auth')

    <div class="flex flex-col gap-6" data-auth-page="settings.security">
        <h1 class="text-2xl font-semibold">{{ __('Security') }}</h1>

        @include('auth::partials.status')

        <x-forms::card :title="__('Update password')" data-password-form>
            <x-forms::form method="PUT" :action="$urls['user-password.update']" class="flex flex-col gap-4">
                <x-forms::password name="current_password" :label="__('Current password')" required autocomplete="current-password" dir="ltr" />

                <x-forms::password name="password" :label="__('New password')" required autocomplete="new-password" dir="ltr" />

                <x-forms::password name="password_confirmation" :label="__('Confirm password')" required autocomplete="new-password" dir="ltr" />

                <div>
                    <x-forms::submit>{{ __('Save password') }}</x-forms::submit>
                </div>
            </x-forms::form>
        </x-forms::card>

        @if($two_factor)
            @include('auth::settings.partials.two-factor')
        @endif

        @if(! is_null($passkeys))
            @include('auth::settings.partials.passkeys')
        @endif

        @if(! is_null($sessions))
            @include('auth::settings.partials.sessions')
        @endif

        @if(! is_null($api_tokens))
            @include('auth::settings.partials.api-tokens')
        @endif
    </div>
@endsection
