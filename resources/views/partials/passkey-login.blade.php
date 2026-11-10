@manifestRequire('auth::passkeys')

<div class="flex flex-col gap-2" data-passkey-login
     data-options-url="{{ $urls['passkey.login-options'] }}"
     data-url="{{ $urls['passkey.login'] }}"
     data-unsupported="{{ __('This browser does not support passkeys.') }}"
     data-failed="{{ __('We could not log you in with a passkey. Please try again.') }}">
    <x-forms::button type="button" color="secondary" class="w-full" data-passkey-login-button>
        {{ __('Log in with a passkey') }}
    </x-forms::button>
    <p class="text-sm text-red-700" role="alert" data-passkey-error hidden></p>
</div>
