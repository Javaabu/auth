<nav class="flex flex-wrap items-center gap-2" aria-label="{{ __('Account') }}" data-account-nav>
    @isset($urls['profile.edit'])
        <x-forms::link-button :url="$urls['profile.edit']" color="secondary" size="sm">{{ __('Profile') }}</x-forms::link-button>
    @endisset

    @isset($urls['security.edit'])
        <x-forms::link-button :url="$urls['security.edit']" color="secondary" size="sm">{{ __('Security') }}</x-forms::link-button>
    @endisset

    @isset($urls['logout'])
        <x-forms::form :action="$urls['logout']" class="inline-flex">
            <x-forms::submit color="secondary" size="sm">{{ __('Log out') }}</x-forms::submit>
        </x-forms::form>
    @endisset
</nav>
