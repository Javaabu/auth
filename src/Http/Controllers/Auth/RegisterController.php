<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Javaabu\Auth\Contracts\RegisterContract;
use Javaabu\Auth\Http\Controllers\AuthBaseController;

abstract class RegisterController extends AuthBaseController implements RegisterContract
{
    use RegistersUsers;

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

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Display the registration form
     */
    public function showRegistrationForm(): View
    {
        return view('admin.auth.register');
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

    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web_admin');
    }
}
