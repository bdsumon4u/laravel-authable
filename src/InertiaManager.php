<?php

namespace Hotash\Authable;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InertiaManager
{
    public static $viewSpace;

    /**
     * The registered rendering callbacks.
     *
     * @var array
     */
    protected $renderingCallbacks = [];

    /**
     * Render the given Inertia page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $page
     * @param  array  $data
     * @return \Inertia\Response
     */
    public function render(Request $request, string $page, array $data = [])
    {
        if (isset($this->renderingCallbacks[$page])) {
            foreach ($this->renderingCallbacks[$page] as $callback) {
                $data = $callback($request, $data);
            }
        }

        if (is_callable($viewSpace = static::$viewSpace)) {
            $viewSpace = $viewSpace();
        }

        $viewSpace = $viewSpace ?? Registrar::viewSpace();

        return Inertia::render($viewSpace.$page, $data);
    }

    /**
     * Register a rendering callback.
     *
     * @param  string  $page
     * @param  callable  $callback
     * @return $this
     */
    public function whenRendering(string $page, callable $callback)
    {
        $this->renderingCallbacks[$page][] = $callback;

        return $this;
    }
}
