<?php

namespace Javaabu\Auth\Routing;

use Illuminate\Routing\Router;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;

/**
 * Registers a user type's routes for `Route::authRoutes('admin', [...])`
 */
class AuthRoutes
{
    /**
     * @param  array{
     *     prefix?: string,
     *     middleware?: string|array<int, string>,
     *     domain?: string|null,
     *     account_prefix?: string,
     *     except?: array<int, string>
     * }  $options
     */
    public static function register(Router $router, ?string $type = null, array $options = []): void
    {
        $user_type = $type ? UserTypes::get($type) : UserTypes::default();

        (new static($router, $user_type, $options))->registerRoutes();
    }

    /** @var array<int, string> */
    protected array $except;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected Router $router,
        protected UserType $type,
        protected array $options = [],
    ) {
        $this->except = $options['except'] ?? [];
    }

    public function registerRoutes(): void
    {
        $attributes = [
            'prefix' => $this->options['prefix'] ?? '',
            'as' => $this->type->routeNamePrefix(),
            'middleware' => (array) ($this->options['middleware'] ?? ['web']),
            UserTypeRegistry::ROUTE_ACTION => $this->type->name(),
        ];

        if (! empty($this->options['domain'])) {
            $attributes['domain'] = $this->options['domain'];
        }

        $this->router->group($attributes, function () {
            $this->guestRoutes();
            $this->signedInRoutes();
            $this->accountRoutes();
        });
    }

    protected function guestRoutes(): void
    {
        $name = $this->type->name();

        $this->router->middleware("auth.guest:{$name}")->group(function () {
            $login = $this->controller('login');

            $this->router->get('login', [$login, 'create'])->name('login');
            $this->router->post('login', [$login, 'store'])->name('login.store');

            if ($this->wants(UserType::REGISTRATION)) {
                $register = $this->controller('register');

                $this->router->get('register', [$register, 'create'])->name('register');
                $this->router->post('register', [$register, 'store'])->name('register.store');
            }

            if ($this->wants(UserType::PASSWORD_RESET)) {
                $link = $this->controller('password_reset_link');
                $new_password = $this->controller('new_password');

                $this->router->get('forgot-password', [$link, 'create'])->name('password.request');
                $this->router->post('forgot-password', [$link, 'store'])->middleware('throttle:auth.password-reset')->name('password.email');
                $this->router->get('reset-password/{token}', [$new_password, 'create'])->name('password.reset');
                $this->router->post('reset-password', [$new_password, 'store'])->middleware('throttle:auth.password-reset')->name('password.update');
            }

            if ($this->type->hasTwoFactor() && $this->wants('two_factor')) {
                $challenge = $this->controller('two_factor_challenge');

                $this->router->get('two-factor-challenge', [$challenge, 'create'])->name('two-factor.login');
                $this->router->post('two-factor-challenge', [$challenge, 'store'])->name('two-factor.login.store');
            }

            if ($this->wants(UserType::PASSKEYS)) {
                $passkey_login = $this->controller('passkey_login');

                $this->router->get('passkeys/login/options', [$passkey_login, 'options'])->middleware('throttle:auth.passkeys')->name('passkey.login-options');
                $this->router->post('passkeys/login', [$passkey_login, 'store'])->middleware('throttle:auth.passkeys')->name('passkey.login');
            }

            foreach ($this->type->loginMethodsList() as $key => $method) {
                if (! in_array("login_method:{$key}", $this->except, true)) {
                    $method->routes($this->router, $this->type);
                }
            }
        });
    }

    /**
     * Pages for signed-in users who may not have finished setting up their account
     */
    protected function signedInRoutes(): void
    {
        $name = $this->type->name();

        $this->router->middleware("auth.type:{$name},basic")->group(function () {
            $this->router->post('logout', [$this->controller('login'), 'destroy'])->name('logout');

            $this->router->get('account/inactive', $this->controller('account_status'))->name('account.inactive');

            if ($this->wants(UserType::EMAIL_VERIFICATION)) {
                $this->router->get('verify-email', $this->controller('email_verification_prompt'))->name('verification.notice');
                $this->router->get('verify-email/{id}/{hash}', $this->controller('verify_email'))
                    ->middleware(['signed', 'throttle:auth.verification'])
                    ->name('verification.verify');
                $this->router->post('email/verification-notification', [$this->controller('email_verification_notification'), 'store'])
                    ->middleware('throttle:auth.verification')
                    ->name('verification.send');
            }

            if ($this->wants(UserType::PASSWORD_CONFIRMATION)) {
                $confirm = $this->controller('confirm_password');

                $this->router->get('confirm-password', [$confirm, 'show'])->name('password.confirm');
                $this->router->post('confirm-password', [$confirm, 'store'])->name('password.confirm.store');

                if ($this->wants(UserType::PASSKEYS)) {
                    $passkey_confirm = $this->controller('passkey_confirmation');

                    $this->router->get('passkeys/confirm/options', [$passkey_confirm, 'options'])->middleware('throttle:auth.passkeys')->name('passkey.confirm-options');
                    $this->router->post('passkeys/confirm', [$passkey_confirm, 'store'])->middleware('throttle:auth.passkeys')->name('passkey.confirm');
                }
            }

            $required = $this->controller('required_password');

            $this->router->get('update-password', [$required, 'edit'])->name('password.required');
            $this->router->put('update-password', [$required, 'update'])->name('password.required.update');
        });
    }

    /**
     * The profile and security pages
     */
    protected function accountRoutes(): void
    {
        $name = $this->type->name();
        $confirm = $this->wants(UserType::PASSWORD_CONFIRMATION) ? ["auth.password.confirm:{$name}"] : [];

        $this->router->middleware("auth.type:{$name}")
            ->prefix($this->options['account_prefix'] ?? 'settings')
            ->group(function () use ($confirm) {
                if ($this->wants(UserType::PROFILE)) {
                    $profile = $this->controller('profile');

                    $this->router->get('profile', [$profile, 'edit'])->name('profile.edit');
                    $this->router->patch('profile', [$profile, 'update'])->name('profile.update');

                    if ($this->wants(UserType::DELETE_ACCOUNT)) {
                        $this->router->delete('profile', [$profile, 'destroy'])->name('profile.destroy');
                    }
                }

                if (! $this->wants('security')) {
                    return;
                }

                $this->router->get('security', [$this->controller('security'), 'edit'])->middleware($confirm)->name('security.edit');
                $this->router->put('password', [$this->controller('password'), 'update'])->middleware('throttle:6,1')->name('user-password.update');

                if ($this->type->hasTwoFactor() && $this->wants('two_factor')) {
                    $two_factor = $this->controller('two_factor');

                    $this->router->middleware($confirm)->group(function () use ($two_factor) {
                        $this->router->post('two-factor', [$two_factor, 'store'])->name('two-factor.enable');
                        $this->router->post('two-factor/confirm', [$two_factor, 'confirm'])->name('two-factor.confirm');
                        $this->router->delete('two-factor', [$two_factor, 'destroy'])->name('two-factor.disable');
                        $this->router->get('two-factor/qr-code', [$two_factor, 'qrCode'])->name('two-factor.qr-code');
                        $this->router->get('two-factor/secret-key', [$two_factor, 'secretKey'])->name('two-factor.secret-key');
                        $this->router->get('two-factor/recovery-codes', [$two_factor, 'recoveryCodes'])->name('two-factor.recovery-codes');
                        $this->router->post('two-factor/recovery-codes', [$two_factor, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-recovery-codes');
                    });
                }

                if ($this->wants(UserType::PASSKEYS)) {
                    $passkeys = $this->controller('passkeys');

                    $this->router->middleware([...$confirm, 'throttle:auth.passkeys'])->group(function () use ($passkeys) {
                        $this->router->get('passkeys/options', [$passkeys, 'options'])->name('passkey.registration-options');
                        $this->router->post('passkeys', [$passkeys, 'store'])->name('passkey.store');
                        $this->router->delete('passkeys/{passkey}', [$passkeys, 'destroy'])->name('passkey.destroy');
                    });
                }

                if ($this->wants(UserType::SESSIONS)) {
                    $this->router->delete('sessions', [$this->controller('sessions'), 'destroy'])->middleware($confirm)->name('sessions.destroy');
                }

                if ($this->wants(UserType::API_TOKENS)) {
                    $tokens = $this->controller('api_tokens');

                    $this->router->post('api-tokens', [$tokens, 'store'])->middleware('throttle:auth.api-tokens')->name('api-tokens.store');
                    $this->router->delete('api-tokens/{token}', [$tokens, 'destroy'])->name('api-tokens.destroy');
                }
            });
    }

    /**
     * Whether to register a feature's routes: the type has it and the macro didn't leave it out
     */
    protected function wants(string $feature): bool
    {
        if (in_array($feature, $this->except, true)) {
            return false;
        }

        if (in_array($feature, ['two_factor', 'security'], true)) {
            return true;
        }

        return $this->type->hasFeature($feature);
    }

    /** @return class-string */
    protected function controller(string $key): string
    {
        return config("javaabu-auth.controllers.{$key}");
    }
}
