@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Choose a new password')" data-auth-page="reset-password">
        <x-forms::form :action="$urls['password.update']" class="flex flex-col gap-4" summary>
            <x-forms::hidden name="token" :default="$token" />

            <x-forms::email :name="$auth_type['username']" :label="__('Email address')" :default="$email" required autocomplete="email" dir="ltr" />

            <x-forms::password name="password" :label="__('New password')" required autofocus autocomplete="new-password" dir="ltr" />

            <x-forms::password name="password_confirmation" :label="__('Confirm password')" required autocomplete="new-password" dir="ltr" />

            <x-forms::submit class="w-full">{{ __('Reset password') }}</x-forms::submit>
        </x-forms::form>
    </x-forms::card>
@endsection
