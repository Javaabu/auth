<?php

namespace Javaabu\Auth\Support;

use Illuminate\Support\Facades\Route;
use Javaabu\Auth\UserType;

/**
 * The props every page gets: the user type, its features and the URLs of its routes
 */
class PageData
{
    /** Routes with parameters, which pages build themselves */
    public const array PARAMETER_ROUTES = [
        'password.reset',
        'verification.verify',
        'passkey.destroy',
        'api-tokens.destroy',
    ];

    /** @return array<string, mixed> */
    public function shared(UserType $type): array
    {
        return [
            'auth_type' => [
                'name' => $type->name(),
                'features' => $type->allFeatures(),
                'two_factor' => $type->twoFactorMode()->value,
                'username' => $type->usernameColumn(),
                'login_methods' => array_values(array_map(fn ($method) => [
                    'key' => $method->key(),
                    'label' => $method->label(),
                    'url' => $method->url($type),
                ], $type->loginMethodsList())),
            ],
            'urls' => $this->urls($type),
            'account_links' => $this->accountLinks($type),
            'status' => session('status'),
        ];
    }

    /**
     * The URLs of the type's routes, keyed by their name without the type prefix
     *
     * @return array<string, string>
     */
    public function urls(UserType $type): array
    {
        $prefix = $type->routeNamePrefix();
        $urls = [];

        foreach (Route::getRoutes()->getRoutesByName() as $name => $route) {
            if ($prefix !== '' && ! str_starts_with($name, $prefix)) {
                continue;
            }

            if ($route->getAction('auth_user_type') !== $type->name()) {
                continue;
            }

            $short_name = substr($name, strlen($prefix));

            if (in_array($short_name, self::PARAMETER_ROUTES, true)) {
                continue;
            }

            $urls[$short_name] = route($name);
        }

        $urls['home'] = $type->homeUrl($type->auth()->user());

        return $urls;
    }

    /**
     * The account navigation of the type: Profile, Security and the links packages added
     *
     * @return array<int, array{key: string, label: string, url: string, active: bool}>
     */
    public function accountLinks(UserType $type): array
    {
        return array_map(fn (AccountLink $link) => $link->toArray($type), $type->accountLinks());
    }

    /**
     * The translated strings of the React pages
     *
     * @return array<string, string>
     */
    public function strings(): array
    {
        $strings = [];

        foreach (Strings::ALL as $key => $english) {
            // Placeholders stay for the page to fill
            $strings[$key] = __($english, []);
        }

        return $strings;
    }
}
