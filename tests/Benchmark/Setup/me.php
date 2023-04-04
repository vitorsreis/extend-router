<?php

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(function ($setting, $benchmark) {
    $title = 'D5WHub Extend Router';
    $argument = $setting['arguments'][':arg'];
    $instance = function () use ($setting, $argument) {
        $instance = new D5WHUB\Extend\Router\Router(new D5WHUB\Extend\Router\Cache\Memory());

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
            return $instance->match('GET', $argument['match']['first'])->execute()->result;
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->match('GET', $argument['match']['last'])->execute()->result;
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['throw' => ['code' => 404, 'class' => D5WHUB\Extend\Router\Exception\RuntimeException::class]],
        (fn ($instance) => function () use ($instance) {
            return $instance->match('GET', '/not-even-real')->execute()->result;
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        ['throw' => ['code' => 405, 'class' => D5WHUB\Extend\Router\Exception\RuntimeException::class]],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->match('POST', $argument['match']['first'])->execute()->result;
        })($instance())
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        ['throw' => ['code' => 405, 'class' => D5WHUB\Extend\Router\Exception\RuntimeException::class]],
        (fn ($instance) => function () use ($instance, $argument) {
            return $instance->match('POST', $argument['match']['last'])->execute()->result;
        })($instance())
    );

    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, &$random) {
            return $instance->match('GET', array_shift($random))->execute()->result;
        })($instance())
    );
})($setting, $benchmark);
