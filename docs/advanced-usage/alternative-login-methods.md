---
title: Alternative login methods
sidebar_position: 1
---

Besides the password form and passkeys, a user type can offer other ways to log in, such as a mobile number and a one-time code (javaabu/mobile-verification plugs in this way). A login method is a class implementing `Javaabu\Auth\Contracts\LoginMethod`:

```php
namespace App\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\Auth\Contracts\LoginMethod;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;

class MobileLoginMethod implements LoginMethod
{
    public function key(): string
    {
        return 'mobile';
    }

    public function label(): string
    {
        return __('Log in with your mobile number');
    }

    /** Registered inside the type's guest route group, so names get the type's prefix */
    public function routes(Router $router, UserType $type): void
    {
        $router->get('login/mobile', [MobileLoginController::class, 'create'])->name('login.mobile');
        $router->post('login/mobile', [MobileLoginController::class, 'store'])->name('login.mobile.store');
    }

    public function url(UserType $type): string
    {
        return $type->route('login.mobile');
    }
}
```

Once the method has identified the user, finish with `StartAuthenticatedSession`. It asks for the two-factor code when the user has it on, otherwise logs in on the type's guard, regenerates the session and returns the login response, exactly as the password form does:

```php
class MobileLoginController
{
    public function store(Request $request, StartAuthenticatedSession $start_session)
    {
        $type = UserTypes::fromRequest($request);

        $user = $type->newModel()->newQuery()->where('mobile', $request->input('mobile'))->first();

        if (! $user || ! $this->codeIsValid($user, $request->input('code'))) {
            throw ValidationException::withMessages(['code' => __('The code is incorrect.')]);
        }

        return $start_session($request, $type, $user, remember: $request->boolean('remember'));
    }
}
```

Pass `skip_two_factor: true` when the method is already two factors.

Add the method to the type:

```php
'admin' => [
    'login_methods' => [App\Auth\MobileLoginMethod::class],
],

// or
UserTypes::get('user')->loginMethod(MobileLoginMethod::class);
```

Register methods before `Route::authRoutes()` runs. The login page shows a link per method (`data-login-method="mobile"`), and the React login page gets them in `auth_type.login_methods`. Leave a method's routes out for one registration with `'except' => ['login_method:mobile']`.
