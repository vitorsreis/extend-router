<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

/** @var array $setting */
/** @var VSR\Extend\Benchmark\Collection[] $benchmark */

(static function ($setting, $benchmark) {
    $title = 'VSR Extend Router';
    $argument = $setting['arguments'][':arg'];
    $instance = static function () use ($setting, $argument) {
        $instance = new VSR\Extend\Router(new VSR\Extend\Router\Cache\Memory());
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

    ($benchmark['first'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn($instance) => static function () use ($instance, $argument) {
            return $instance->match('GET', $argument['match']['first'])->execute()->result;
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn($instance) => static function () use ($instance, $argument) {
            return $instance->match('GET', $argument['match']['last'])->execute()->result;
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['throw' => ['code' => 404, 'class' => VSR\Extend\Router\Exception\RuntimeException::class]],
        (static fn($instance) => static function () use ($instance) {
            return $instance->match('GET', '/not-even-real')->execute()->result;
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        ['throw' => ['code' => 405, 'class' => VSR\Extend\Router\Exception\RuntimeException::class]],
        (static fn($instance) => static function () use ($instance, $argument) {
            return $instance->match('POST', $argument['match']['first'])->execute()->result;
        })($instance())
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        ['throw' => ['code' => 405, 'class' => VSR\Extend\Router\Exception\RuntimeException::class]],
        (static fn($instance) => static function () use ($instance, $argument) {
            return $instance->match('POST', $argument['match']['last'])->execute()->result;
        })($instance())
    );

    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn($instance) => static function () use ($instance, &$random) {
            return $instance->match('GET', array_shift($random))->execute()->result;
        })($instance())
    );
})($setting, $benchmark);
