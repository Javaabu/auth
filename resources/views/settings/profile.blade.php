@extends($layout)

@section($layout_section)
    @manifestRequire('auth::auth')

    <div class="flex flex-col gap-6" data-auth-page="settings.profile">
        <h1 class="text-2xl font-semibold">{{ __('Profile') }}</h1>

        @include('auth::partials.status')

        <x-forms::card :title="__('Profile information')">
            <x-forms::form method="PATCH" :action="$urls['profile.update']" :model="$profile" class="flex flex-col gap-4" data-profile-form>
                <x-forms::text name="name" :label="__('Name')" required maxlength="255" autocomplete="name" dir="auto" />

                <x-forms::email :name="$auth_type['username']" :label="__('Email address')" required autocomplete="email" dir="ltr" />

                @if($must_verify_email)
                    <x-forms::alert type="warning">
                        {{ __('Your email address is not verified.') }}
                        @isset($urls['verification.send'])
                            <x-forms::button type="submit" form="send-verification" color="secondary" size="sm">{{ __('Resend verification email') }}</x-forms::button>
                        @endisset
                    </x-forms::alert>
                @endif

                <div>
                    <x-forms::submit>{{ __('Save') }}</x-forms::submit>
                </div>
            </x-forms::form>

            @if($must_verify_email && isset($urls['verification.send']))
                <form id="send-verification" method="POST" action="{{ $urls['verification.send'] }}" hidden>@csrf</form>
            @endif
        </x-forms::card>

        @if($can_delete_account)
            <x-forms::card :title="__('Delete account')" data-delete-account>
                <x-forms::form method="DELETE" :action="$urls['profile.destroy']" class="flex flex-col gap-4"
                               data-confirm="{{ __('Are you sure you want to delete your account? This cannot be undone.') }}">
                    <p class="text-sm">{{ __('Once your account is deleted, all of its data is permanently deleted. Enter your password to confirm.') }}</p>

                    <x-forms::password name="password" :label="__('Password')" required autocomplete="current-password" dir="ltr" />

                    <div>
                        <x-forms::submit color="danger">{{ __('Delete account') }}</x-forms::submit>
                    </div>
                </x-forms::form>
            </x-forms::card>
        @endif
    </div>
@endsection
