<?php

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(function ($setting, $benchmark) {
    $title = 'Aura Router';
    $argument = $setting['arguments']['{arg}'];
    $instance = function () use ($setting, $argument) {
        $instance = new Aura\Router\RouterContainer();

        for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
            [ $route, $url ] = $argument['routes'][$i];
            if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                $instance->getMap()->get("$i", $route, fn() => 'TEST');
            } else {
                match ($cursor) {
                    1       => $instance->getMap()->get("$i", $route, fn() => 'TEST'),
                    2       => $instance->getMap()->post("$i", $route, fn() => 'TEST'),
                    3       => $instance->getMap()->put("$i", $route, fn() => 'TEST'),
                    4       => $instance->getMap()->patch("$i", $route, fn() => 'TEST'),
                    5       => $instance->getMap()->delete("$i", $route, fn() => 'TEST'),
                    6       => $instance->getMap()->options("$i", $route, fn() => 'TEST'),
                    7       => $instance->getMap()->head("$i", $route, fn() => 'TEST'),
                    default => $instance->getMap()->route("$i", $route, fn() => 'TEST')
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
            return ($instance->getMatcher()->match(Zend\Diactoros\ServerRequestFactory::fromGlobals(
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $argument['match']['first']],
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ))->handler)();
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, $argument) {
            return ($instance->getMatcher()->match(Zend\Diactoros\ServerRequestFactory::fromGlobals(
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $argument['match']['last']],
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ))->handler)();
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['return' => 'Aura\Router\Rule\Path'],
        (fn ($instance) => function () use ($instance) {
            $instance->getMatcher()->match(Zend\Diactoros\ServerRequestFactory::fromGlobals(
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/not-even-real'],
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ));
            return $instance->getMatcher()->getFailedRoute()->failedRule;
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        ['return' => 'Aura\Router\Rule\Allows'],
        (fn ($instance) => function () use ($instance, $argument) {
            $instance->getMatcher()->match(Zend\Diactoros\ServerRequestFactory::fromGlobals(
                ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => $argument['match']['first']],
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ));
            return $instance->getMatcher()->getFailedRoute()->failedRule;
        })($instance())
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        ['return' => 'Aura\Router\Rule\Allows'],
        (fn ($instance) => function () use ($instance, $argument) {
            $instance->getMatcher()->match(Zend\Diactoros\ServerRequestFactory::fromGlobals(
                ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => $argument['match']['last']],
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ));
            return $instance->getMatcher()->getFailedRoute()->failedRule;
        })($instance())
    );

    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (fn ($instance) => function () use ($instance, &$random) {
            return ($instance->getMatcher()->match(Zend\Diactoros\ServerRequestFactory::fromGlobals(
                ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => array_shift($random)],
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ))->handler)();
        })($instance())
    );
})($setting, $benchmark);
