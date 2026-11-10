@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Choose a new password')" data-auth-page="update-required-password">
        <div class="flex flex-col gap-4">
            <p class="text-sm">{{ __('You need to choose a new password before you continue.') }}</p>

            <x-forms::form method="PUT" :action="$urls['password.required.update']" class="flex flex-col gap-4" summary>
                <x-forms::password name="current_password" :label="__('Current password')" required autocomplete="current-password" dir="ltr" />

                <x-forms::password name="password" :label="__('New password')" required autocomplete="new-password" dir="ltr" />

                <x-forms::password name="password_confirmation" :label="__('Confirm password')" required autocomplete="new-password" dir="ltr" />

                <x-forms::submit class="w-full">{{ __('Save password') }}</x-forms::submit>
            </x-forms::form>
        </div>
    </x-forms::card>
@endsection
