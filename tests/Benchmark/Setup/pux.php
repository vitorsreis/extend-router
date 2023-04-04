<?php

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(function ($setting, $benchmark) {
    $title = extension_loaded('pux') ? 'Pux EXT' : 'Pux PHP';
    $argument = $setting['arguments'][':arg'];
    $instance = function () use ($setting, $argument) {
        $instance = new Pux\Mux();

        for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
            [ $route, $url ] = $argument['routes'][$i];
            if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                $instance->get($route, fn() => 'TEST');
            } else {
                match ($cursor) {
                    1       => $instance->get($route, fn() => 'TEST'),
                    2       => $instance->post($route, fn() => 'TEST'),
                    3       => $instance->put($route, fn() => 'TEST'),
                    4       => $instance->patch($route, fn() => 'TEST'),
                    5       => $instance->delete($route, fn() => 'TEST'),
                    6       => $instance->options($route, fn() => 'TEST'),
                    7       => $instance->head($route, fn() => 'TEST'),
                    default => $instance->any($route, fn() => 'TEST')
                };
                $cursor = $cursor > 7 ? 0 : $cursor;
            }
        }

        return $instance;
    };

    ($benchmark['instantiation'] ?? null)?->addTest(
        $title,
        ['throw' => null],
        $instance
    );

    if (!empty($setting['one_instance'])) {
        $instance = $instance();
        $instance = fn() => $instance;
    }

    ($benchmark['first'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->match(
                $argument['match']['first'],
                new Pux\RouteRequest('GET', $argument['match']['first'])
            )[2]();
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->match(
                $argument['match']['last'],
                new Pux\RouteRequest('GET', $argument['match']['last'])
            )[2]();
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['return' => null],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->match('/not-even-real', new Pux\RouteRequest('GET', '/not-even-real'));
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        [ 'skipped' => "$title doesn't natively have method not allowed" ]
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        [ 'skipped' => "$title doesn't natively have method not allowed" ]
    );

    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, &$random) {
            $url = array_shift($random);
            return $instance->match($url, new Pux\RouteRequest('GET', $url))[2]();
        })($instance())
    );
})($setting, $benchmark);
