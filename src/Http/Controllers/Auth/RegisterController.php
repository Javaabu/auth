<?php

namespace Javaabu\Auth\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Contracts\Foundation\Application;
use Javaabu\Auth\Http\Controllers\AuthBaseController;
use Javaabu\Auth\Models\User;
use Javaabu\Auth\User as UserContract;

abstract class RegisterController extends AuthBaseController
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
     * @param  array  $data
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
     * @param  array  $data
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
     *
     * @return Application|Factory|Response|View
     */
    public function showRegistrationForm()
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
        return with(new User())->homeUrl();
    }

    /**
     * Determine the User Model to use when determining the path for redirect.
     * Should return new
     *
     * @return UserContract
     */
    public function determinePathForRedirectUsing(): UserContract
    {
        return new User();
    }

    /**
     * Apply middlewares for the controller. Used in the constructor.
     * Helps with applying/changing applied middlewares for the controller.
     *
     * @return void
     */
    public function applyMiddlewares(): void
    {
        $this->middleware('guest:web_admin');
    }
}
