@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Verify your email address')" data-auth-page="verify-email">
        <div class="flex flex-col gap-4">
            <p class="text-sm">{{ __('We have emailed a verification link to :email. Click the link to verify your email address.', ['email' => $email]) }}</p>

            @include('auth::partials.status')

            <div class="flex flex-wrap items-center justify-between gap-2">
                <x-forms::form :action="$urls['verification.send']">
                    <x-forms::submit>{{ __('Resend verification email') }}</x-forms::submit>
                </x-forms::form>

                <x-forms::form :action="$urls['logout']">
                    <x-forms::submit color="secondary">{{ __('Log out') }}</x-forms::submit>
                </x-forms::form>
            </div>
        </div>
    </x-forms::card>
@endsection
