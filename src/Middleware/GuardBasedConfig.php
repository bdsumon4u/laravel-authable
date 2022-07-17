<?php

namespace Hotash\Authable\Middleware;

use Closure;
use Hotash\Authable\Registrar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuardBasedConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($guard = Registrar::guard()) {
            config(['fortify.guard' => $guard]);
            config(['jetstream.guard' => $guard]);

            config(['fortify.passwords' => Str::plural($guard)]);

            foreach (Registrar::features() as $key => $value) {
                config([$key.'.features' => $value]);
            }
        }

        return $next($request);
    }
}
