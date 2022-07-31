<?php

namespace Hotash\Authable\Providers;

use Hotash\Authable\InertiaManager;
use Hotash\Authable\Middleware\GuardBasedConfig;
use Hotash\Authable\Registrar;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;
use Symfony\Component\HttpFoundation\Response;

class AuthableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register()
    {
        Jetstream::$inertiaManager = new InertiaManager;
        $this->app->make(Kernel::class)->pushMiddleware(GuardBasedConfig::class);
    }

    public static function fortify()
    {
        Fortify::authenticateUsing(function (Request $request) {
            $user = Registrar::model()::where('email', $request->email)->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {
                return $user;
            }
        });

        Fortify::loginView(function () {
            return Inertia::render(Registrar::viewSpace().'Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'status' => session('status'),
            ]);
        });

        Fortify::requestPasswordResetLinkView(function () {
            abort_unless(in_array(Features::resetPasswords(), Registrar::features(key: 'fortify')), Response::HTTP_NOT_FOUND);

            return Inertia::render(Registrar::viewSpace().'Auth/ForgotPassword', [
                'status' => session('status'),
            ]);
        });

        Fortify::resetPasswordView(function (Request $request) {
            abort_unless(in_array(Features::resetPasswords(), Registrar::features(key: 'fortify')), Response::HTTP_NOT_FOUND);

            return Inertia::render(Registrar::viewSpace().'Auth/ResetPassword', [
                'email' => $request->input('email'),
                'token' => $request->route('token'),
            ]);
        });

        Fortify::registerView(function () {
            abort_unless(in_array(Features::registration(), Registrar::features(key: 'fortify')), Response::HTTP_NOT_FOUND);

            return Inertia::render(Registrar::viewSpace().'Auth/Register');
        });

        Fortify::verifyEmailView(function () {
            abort_unless(in_array(Features::emailVerification(), Registrar::features(key: 'fortify')), Response::HTTP_NOT_FOUND);

            return Inertia::render(Registrar::viewSpace().'Auth/VerifyEmail', [
                'status' => session('status'),
            ]);
        });

        Fortify::twoFactorChallengeView(function () {
            abort_unless(in_array(Features::twoFactorAuthentication(), Registrar::features(key: 'fortify')), Response::HTTP_NOT_FOUND);

            return Inertia::render(Registrar::viewSpace().'Auth/TwoFactorChallenge');
        });

        Fortify::confirmPasswordView(function () {
            return Inertia::render(Registrar::viewSpace().'Auth/ConfirmPassword');
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->runningInConsole()) {
            //
        }

        foreach (Registrar::guards() as $guard) {
            if (File::isFile($path = base_path('routes/'.$guard.'.php'))) {
                Route::domain(guard_url($guard))
                    ->as($guard.'.')->middleware(['web'])
                    ->group(fn () => $this->loadRoutesFrom($path));
            }
        }
    }
}
