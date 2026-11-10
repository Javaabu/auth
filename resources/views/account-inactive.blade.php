@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Your account is not active')" data-auth-page="account-inactive">
        <div class="flex flex-col gap-4">
            <x-forms::alert type="warning">{{ $message }}</x-forms::alert>

            <div class="flex flex-wrap items-center justify-between gap-2">
                @isset($urls['password.request'])
                    <x-forms::link-button :url="$urls['password.request']" color="secondary">{{ __('Reset your password') }}</x-forms::link-button>
                @endisset

                <x-forms::form :action="$urls['logout']">
                    <x-forms::submit color="secondary">{{ __('Log out') }}</x-forms::submit>
                </x-forms::form>
            </div>
        </div>
    </x-forms::card>
@endsection
