<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(static function ($setting, $benchmark) {

    $title = extension_loaded('pux') ? 'Pux EXT' : 'Pux PHP';
    $argument = $setting['arguments'][':arg'];
    $instance = static function () use ($setting, $argument) {

        $instance = new Pux\Mux();

        for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
            [$route, $url] = $argument['routes'][$i];
            if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                $instance->get($route, static fn() => 'TEST');
            } else {
                match ($cursor) {
                    1 => $instance->get($route, static fn() => 'TEST'),
                    2 => $instance->post($route, static fn() => 'TEST'),
                    3 => $instance->put($route, static fn() => 'TEST'),
                    4 => $instance->patch($route, static fn() => 'TEST'),
                    5 => $instance->delete($route, static fn() => 'TEST'),
                    6 => $instance->options($route, static fn() => 'TEST'),
                    7 => $instance->head($route, static fn() => 'TEST'),
                    default => $instance->any($route, static fn() => 'TEST')
                };
                $cursor = $cursor > 7 ? 0 : $cursor;
            }
        }

        return $instance;
    };
    ($benchmark['instantiation'] ?? null)?->addTest($title, ['throw' => null], $instance);
    if (!empty($setting['one_instance'])) {
        $instance = $instance();
        $instance = static fn() => $instance;
    }

    ($benchmark['first'] ?? null)?->addTest($title, ['return' => 'TEST'], (static fn($instance) => static function () use ($instance, $argument) {

            return $instance->match($argument['match']['first'], new Pux\RouteRequest('GET', $argument['match']['first']))[2]();
    })($instance()));
    ($benchmark['last'] ?? null)?->addTest($title, ['return' => 'TEST'], (static fn($instance) => static function () use ($instance, $argument) {

            return $instance->match($argument['match']['last'], new Pux\RouteRequest('GET', $argument['match']['last']))[2]();
    })($instance()));
    ($benchmark['not-found'] ?? null)?->addTest($title, ['return' => null], (static fn($instance) => static function () use ($instance) {

            return $instance->match('/not-even-real', new Pux\RouteRequest('GET', '/not-even-real'));
    })($instance()));
    ($benchmark['first-not-allowed'] ?? null)?->addTest($title, ['skipped' => "$title doesn't natively have method not allowed"]);
    ($benchmark['last-not-allowed'] ?? null)?->addTest($title, ['skipped' => "$title doesn't natively have method not allowed"]);
    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest($title, ['return' => 'TEST'], (static fn($instance) => static function () use (&$random, $instance) {

            $url = array_shift($random);
return $instance->match($url, new Pux\RouteRequest('GET', $url))[2]();
    })($instance()));
})($setting, $benchmark);
