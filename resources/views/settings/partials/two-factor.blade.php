<x-forms::card :title="__('Two-factor authentication')" data-two-factor>
    <div class="flex flex-col gap-4">
        @if($two_factor['enabled'])
            <x-forms::alert type="success">{{ __('Two-factor authentication is on.') }}</x-forms::alert>
        @elseif($two_factor['pending'])
            <p class="text-sm">{{ __('Scan the QR code with your authenticator app, then enter the code it shows.') }}</p>

            <div class="flex flex-col items-start gap-2" data-two-factor-qr-code>
                <div class="rounded-sm bg-white p-2">{!! $two_factor['qr_code'] !!}</div>
                <p class="text-sm">
                    {{ __('Setup key') }}:
                    <code dir="ltr" class="font-mono">{{ $two_factor['secret_key'] }}</code>
                </p>
            </div>

            <x-forms::form :action="$urls['two-factor.confirm']" class="flex flex-col gap-4" data-two-factor-confirm>
                <x-forms::text name="code" :label="__('Code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required dir="ltr" />

                <div>
                    <x-forms::submit>{{ __('Confirm') }}</x-forms::submit>
                </div>
            </x-forms::form>
        @else
            <p class="text-sm">
                @if($two_factor['required'])
                    {{ __('Your account needs two-factor authentication. Set it up to continue.') }}
                @else
                    {{ __('Add a second step to your login with a code from an authenticator app.') }}
                @endif
            </p>

            <x-forms::form :action="$urls['two-factor.enable']">
                <x-forms::submit>{{ __('Turn on two-factor authentication') }}</x-forms::submit>
            </x-forms::form>
        @endif

        @if($two_factor['recovery_codes'])
            <div class="flex flex-col gap-2" data-recovery-codes>
                <p class="text-sm">{{ __('Store these recovery codes somewhere safe. Each can be used once if you lose your authenticator app.') }}</p>
                <ul class="grid gap-1 rounded-sm bg-gray-100 p-4 font-mono text-sm sm:grid-cols-2" dir="ltr">
                    @foreach($two_factor['recovery_codes'] as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($two_factor['enabled'])
            <div class="flex flex-wrap gap-2">
                <x-forms::form :action="$urls['two-factor.regenerate-recovery-codes']">
                    <x-forms::submit color="secondary">{{ __('Regenerate recovery codes') }}</x-forms::submit>
                </x-forms::form>

                @unless($two_factor['required'])
                    <x-forms::form method="DELETE" :action="$urls['two-factor.disable']">
                        <x-forms::submit color="danger">{{ __('Turn off two-factor authentication') }}</x-forms::submit>
                    </x-forms::form>
                @endunless
            </div>
        @endif
    </div>
</x-forms::card>
