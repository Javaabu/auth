@manifestRequire('auth::passkeys')

<x-forms::card :title="__('Passkeys')" data-passkeys
               data-options-url="{{ $urls['passkey.registration-options'] }}"
               data-url="{{ $urls['passkey.store'] }}"
               data-unsupported="{{ __('This browser does not support passkeys.') }}"
               data-failed="{{ __('We could not add the passkey. Please try again.') }}">
    <div class="flex flex-col gap-4">
        <p class="text-sm">{{ __('Log in with your fingerprint, face or screen lock instead of a password.') }}</p>

        @if($passkeys)
            <ul class="flex flex-col divide-y divide-gray-200" data-passkey-list>
                @foreach($passkeys as $passkey)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2" data-passkey="{{ $passkey['id'] }}">
                        <div class="flex flex-col">
                            <span class="font-medium" dir="auto">{{ $passkey['name'] }}</span>
                            <span class="text-sm text-gray-600">
                                {{ $passkey['authenticator'] ?? __('Passkey') }} ·
                                {{ __('Added :time', ['time' => $passkey['created_at']]) }}
                                @if($passkey['last_used_at'])
                                    · {{ __('Last used :time', ['time' => $passkey['last_used_at']]) }}
                                @endif
                            </span>
                        </div>

                        <x-forms::form method="DELETE" :action="route($user_type->routeNameFor('passkey.destroy'), $passkey['id'])"
                                       data-confirm="{{ __('Remove this passkey?') }}">
                            <x-forms::submit color="danger" size="sm">{{ __('Remove') }}</x-forms::submit>
                        </x-forms::form>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-600">{{ __('You have no passkeys yet.') }}</p>
        @endif

        <div class="flex flex-wrap items-end gap-2">
            <x-forms::text name="passkey_name" :label="__('Passkey name')" :placeholder="__('e.g. Work laptop')" :model="false" maxlength="255" dir="auto" data-passkey-name />
            <x-forms::button type="button" data-passkey-add-button>{{ __('Add a passkey') }}</x-forms::button>
        </div>
        <p class="text-sm text-red-700" role="alert" data-passkey-error hidden></p>
    </div>
</x-forms::card>
