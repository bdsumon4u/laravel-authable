<?php

use Illuminate\Support\Str;

if (! function_exists('guard_url')) {
    function guard_url($guard = null, $path = null, $parameters = [], $secure = null): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $guardHost = $guard.($guard ? '.' : null).$host;
        if (is_null($path)) {
            return $guardHost;
        }

        $url = url($path, $parameters, $secure);
        if (parse_url($url, PHP_URL_HOST) !== $guardHost) {
            return str_replace($host, $guardHost, $url);
        }

        return $url;
    }
}

if (! function_exists('is_guard')) {
    function is_guard($guard): bool
    {
        return Str::startsWith(request()->getHost(), $guard.'.');
    }
}
