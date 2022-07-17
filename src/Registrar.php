<?php

namespace Hotash\Authable;

use App\Models\User;
use Illuminate\Support\Str;

abstract class Registrar
{
    private static array $stack = [];

    /**
     * @return array
     */
    public static function all(): array
    {
        return static::$stack;
    }

    /**
     * @return array
     */
    public static function guards(): array
    {
        return array_keys(static::$stack);
    }

    /**
     * @param  string  $guard
     * @param  string  $model
     * @param  array  $features
     * @param  callable|null  $viewSpace
     * @return void
     */
    public static function add(string $guard, string $model, array $features = [], callable $viewSpace = null): void
    {
        static::$stack[$guard] = compact('model', 'features', 'viewSpace');

        $plural = Str::plural($guard);

        config([
            'auth.guards.'.$guard => array_merge([
                'driver' => 'session',
                'provider' => $plural,
            ], config('auth.guards.'.$guard, [])),
        ]);

        config([
            'auth.providers.'.$plural => [
                'driver' => 'eloquent',
                'model' => $model,
            ],
        ]);

        config([
            'auth.passwords.'.$plural => [
                'provider' => $plural,
                'table' => 'password_resets',
                'expire' => 60,
                'throttle' => 60,
            ],
        ]);
    }

    /**
     * @return string
     */
    public static function guard(): string
    {
        foreach (static::$stack as $guard => $data) {
            if (is_guard($guard)) {
                return $guard;
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public static function model(): string
    {
        return static::$stack[static::guard()]['model'] ?? User::class;
    }

    /**
     * @return string
     */
    public static function as(): string
    {
        if ($guard = static::guard()) {
            return $guard.'.';
        }

        return '';
    }

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function features(string $key = null): array
    {
        $features = static::$stack[static::guard()]['features'] ?? [
            'fortify' => config('fortify.features', []),
            'jetstream' => config('jetstream.features', []),
        ];

        return $features[$key] ?? $features;
    }

    /**
     * @return string
     */
    public static function viewSpace(): string
    {
        if (! $guard = static::guard()) {
            return '';
        }

        if (! $callback = static::$stack[$guard]['viewSpace']) {
            return '';
        }

        return $callback($guard);
    }
}
