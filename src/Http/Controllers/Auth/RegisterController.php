<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Javaabu\Auth\Contracts\RegisterContract;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Traits\DeterminesRedirectPaths;

abstract class RegisterController extends AuthBaseController implements RegisterContract
{
    use RegistersUsers;
    use DeterminesRedirectPaths;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->applyMiddlewares();
    }

    public function applyMiddlewares(): void
    {
        $this->middleware('guest:' . $this->guardName());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $user_class = $this->userClass();

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique((new $user_class())->getTable(), 'email')],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    public function create(array $data)
    {
        $class = $this->userClass();

        $user = new $class();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];

        $user->save();

        return $user;
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return with($this->determinePathForRedirectUsing())->homeUrl();
    }
}
