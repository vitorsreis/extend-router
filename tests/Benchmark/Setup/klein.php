<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

/** @var array $setting */
/** @var VSR\Extend\Benchmark\Collection[] $benchmark */

(static function ($setting, $benchmark) {
    $title = 'Klein.php';
    $argument = $setting['arguments']['[*:arg]'];
    $instance = static function () use ($setting, $argument) {
        $instance = @new Klein\Klein();
        for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
            [$route, $url] = $argument['routes'][$i];
            if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                $method = 'GET';
            } else {
                $method = match ($cursor) {
                    1 => 'GET',
                    2 => 'POST',
                    3 => 'PUT',
                    4 => 'PATCH',
                    5 => 'DELETE',
                    6 => 'OPTIONS',
                    7 => 'HEAD',
                    default => ''
                };
                $cursor = $cursor > 7 ? 0 : $cursor;
            }
            $instance->respond($method, $route, static fn() => 'TEST');
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
            $instance->dispatch(new Klein\Request(
                $_GET,
                $_POST,
                $_COOKIE,
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $argument['match']['first']],
                $_FILES
            ), $responde = new Klein\Response(), false);
            return $responde->body();
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn($instance) => static function () use ($instance, $argument) {
            $instance->dispatch(new Klein\Request(
                $_GET,
                $_POST,
                $_COOKIE,
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $argument['match']['last']],
                $_FILES
            ), $responde = new Klein\Response(), false);
            return $responde->body();
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['return' => 404],
        (static fn($instance) => static function () use ($instance) {
            $instance->dispatch(new Klein\Request(
                $_GET,
                $_POST,
                $_COOKIE,
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/not-even-real'],
                $_FILES
            ), $responde = new Klein\Response(), false);
            return $responde->status()->getCode();
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        ['return' => 405],
        (static fn($instance) => static function () use ($instance, $argument) {
            $instance->dispatch(new Klein\Request(
                $_GET,
                $_POST,
                $_COOKIE,
                ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => $argument['match']['first']],
                $_FILES
            ), $responde = new Klein\Response(), false);
            return $responde->status()->getCode();
        })($instance())
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        ['return' => 405],
        (static fn($instance) => static function () use ($instance, $argument) {
            $instance->dispatch(new Klein\Request(
                $_GET,
                $_POST,
                $_COOKIE,
                ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => $argument['match']['last']],
                $_FILES
            ), $responde = new Klein\Response(), false);
            return $responde->status()->getCode();
        })($instance())
    );
    $random = $argument['match']['rand'];

    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn($instance) => static function () use ($instance, &$random) {
            $instance->dispatch(new Klein\Request(
                $_GET,
                $_POST,
                $_COOKIE,
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => array_shift($random)],
                $_FILES
            ), $responde = new Klein\Response(), false);
            return $responde->body();
        })($instance())
    );
})($setting, $benchmark);
