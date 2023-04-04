<?php

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(function ($setting, $benchmark) {
    $title = 'FastRoute';
    $argument = $setting['arguments']['{arg}'];
    $instance = function () use ($setting, $argument) {
        return FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) use ($setting, $argument) {
            for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
                [ $route, $url ] = $argument['routes'][$i];
                if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                    $r->get($route, fn() => 'TEST');
                } else {
                    match ($cursor) {
                        1       => $r->get($route, fn() => 'TEST'),
                        2       => $r->post($route, fn() => 'TEST'),
                        3       => $r->put($route, fn() => 'TEST'),
                        4       => $r->patch($route, fn() => 'TEST'),
                        5       => $r->delete($route, fn() => 'TEST'),
                        6       => $r->options($route, fn() => 'TEST'),
                        7       => $r->head($route, fn() => 'TEST'),
                        default => $r->any($route, fn() => 'TEST')
                    };
                    $cursor = $cursor > 7 ? 0 : $cursor;
                }
            }
        });
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
        ['return' => [FastRoute\Dispatcher::FOUND, 'TEST']],
        (fn ($instance) => function () use ($instance, $argument) {
            $match = $instance->dispatch('GET', $argument['match']['first']);
            return [$match[0], $match[1]()];
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => [FastRoute\Dispatcher::FOUND, 'TEST']],
        (fn ($instance) => function () use ($instance, $argument) {
            $match = $instance->dispatch('GET', $argument['match']['last']);
            return [$match[0], $match[1]()];
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['return' => FastRoute\Dispatcher::NOT_FOUND],
        (fn ($instance) => function () use ($instance) {
            return $instance->dispatch('GET', '/not-even-real')[0];
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        ['return' => FastRoute\Dispatcher::METHOD_NOT_ALLOWED],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->dispatch('POST', $argument['match']['first'])[0];
        })($instance())
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        ['return' => FastRoute\Dispatcher::METHOD_NOT_ALLOWED],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->dispatch('POST', $argument['match']['last'])[0];
        })($instance())
    );

    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => [FastRoute\Dispatcher::FOUND, 'TEST']],
        (fn ($instance) => function () use ($instance, &$random) {
            $match = $instance->dispatch('GET', array_shift($random));
            return [$match[0], $match[1]()];
        })($instance())
    );
})($setting, $benchmark);
