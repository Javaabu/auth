{{--
    The account navigation: Profile, Security, the links packages added with
    UserType::accountLink() or UserTypes::accountLink(), and Log out.
    Works on pages that didn't come from the package too (it finds the type from the route).
--}}
@php
    $nav_type = $user_type ?? \Javaabu\Auth\UserTypes::fromRequest() ?? \Javaabu\Auth\UserTypes::default();
    $nav_links = $account_links ?? app(\Javaabu\Auth\Support\PageData::class)->accountLinks($nav_type);
    $nav_logout = $urls['logout'] ?? ($nav_type->hasRoute('logout') ? $nav_type->route('logout') : null);
@endphp

<nav class="flex flex-wrap items-center gap-2" aria-label="{{ __('Account') }}" data-account-nav>
    @foreach($nav_links as $link)
        <x-forms::link-button
            :url="$link['url']"
            :color="$link['active'] ? 'primary' : 'secondary'"
            size="sm"
            data-account-link="{{ $link['key'] }}"
            :aria-current="$link['active'] ? 'page' : null">
            {{ $link['label'] }}
        </x-forms::link-button>
    @endforeach

    @if($nav_logout)
        <x-forms::form :action="$nav_logout" class="inline-flex">
            <x-forms::submit color="secondary" size="sm">{{ __('Log out') }}</x-forms::submit>
        </x-forms::form>
    @endif
</nav>
