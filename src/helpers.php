<?php

if (!function_exists('settings')) {
    /**
     * @param  array|string|null $key
     * @param  mixed  $default
     * @return \Hogus\LaravelSettings\Settings|mixed
     *
     * @throws Exception
     */
    function settings($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('settings');
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                app('settings')->set($k, $v);
            }
            return;
        }

        return app('settings')->get($key, $default);
    }
}
