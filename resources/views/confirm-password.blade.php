@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Confirm your password')" data-auth-page="confirm-password">
        <div class="flex flex-col gap-4">
            <p class="text-sm">{{ __('This is a secure area. Please confirm your password before continuing.') }}</p>

            <x-forms::form :action="$urls['password.confirm.store']" class="flex flex-col gap-4">
                <x-forms::password name="password" :label="__('Password')" required autofocus autocomplete="current-password" dir="ltr" />

                <x-forms::submit class="w-full">{{ __('Confirm') }}</x-forms::submit>
            </x-forms::form>

            @if($can_use_passkeys)
                @manifestRequire('auth::passkeys')

                <div class="flex flex-col gap-2" data-passkey-confirm
                     data-options-url="{{ $urls['passkey.confirm-options'] }}"
                     data-url="{{ $urls['passkey.confirm'] }}"
                     data-unsupported="{{ __('This browser does not support passkeys.') }}"
                     data-failed="{{ __('We could not confirm with a passkey. Please try again.') }}">
                    <x-forms::button type="button" color="secondary" class="w-full" data-passkey-confirm-button>
                        {{ __('Confirm with a passkey') }}
                    </x-forms::button>
                    <p class="text-sm text-red-700" role="alert" data-passkey-error hidden></p>
                </div>
            @endif
        </div>
    </x-forms::card>
@endsection
