@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Two-factor authentication')" data-auth-page="two-factor-challenge">
        <div class="flex flex-col gap-4" data-two-factor-challenge>
            <x-forms::form :action="$urls['two-factor.login.store']" class="flex flex-col gap-4" data-two-factor-code>
                <p class="text-sm">{{ __('Enter the code from your authenticator app.') }}</p>

                <x-forms::text
                    name="code"
                    :label="__('Code')"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    maxlength="6"
                    dir="ltr" />

                <x-forms::submit class="w-full">{{ __('Continue') }}</x-forms::submit>
            </x-forms::form>

            <details class="flex flex-col gap-4" @if($errors->has('recovery_code')) open @endif>
                <summary class="cursor-pointer text-sm underline">{{ __('Use a recovery code instead') }}</summary>

                <x-forms::form :action="$urls['two-factor.login.store']" class="mt-4 flex flex-col gap-4" data-two-factor-recovery>
                    <x-forms::text name="recovery_code" :label="__('Recovery code')" autocomplete="one-time-code" dir="ltr" />

                    <x-forms::submit class="w-full">{{ __('Continue') }}</x-forms::submit>
                </x-forms::form>
            </details>
        </div>
    </x-forms::card>
@endsection
