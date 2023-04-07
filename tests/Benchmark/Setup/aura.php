<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(static function ($setting, $benchmark) {
    $title = 'Aura Router';
    $argument = $setting['arguments']['{arg}'];
    $instance = static function () use ($setting, $argument) {
        $instance = new Aura\Router\RouterContainer();

        for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
            [ $route, $url ] = $argument['routes'][$i];
            if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                $instance->getMap()->get((string)$i, $route, static fn() => 'TEST');
            } else {
                match ($cursor) {
                    1       => $instance->getMap()->get((string)$i, $route, static fn() => 'TEST'),
                    2       => $instance->getMap()->post((string)$i, $route, static fn() => 'TEST'),
                    3       => $instance->getMap()->put((string)$i, $route, static fn() => 'TEST'),
                    4       => $instance->getMap()->patch((string)$i, $route, static fn() => 'TEST'),
                    5       => $instance->getMap()->delete((string)$i, $route, static fn() => 'TEST'),
                    6       => $instance->getMap()->options((string)$i, $route, static fn() => 'TEST'),
                    7       => $instance->getMap()->head((string)$i, $route, static fn() => 'TEST'),
                    default => $instance->getMap()->route((string)$i, $route, static fn() => 'TEST')
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
        $instance = static fn() => $instance;
    }

    ($benchmark['first'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn ($instance) => static function () use ($instance, $argument) {
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
        (static fn ($instance) => static function () use ($instance, $argument) {
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
        (static fn ($instance) => static function () use ($instance) {
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
        (static fn ($instance) => static function () use ($instance, $argument) {
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
        (static fn ($instance) => static function () use ($instance, $argument) {
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
        (static fn ($instance) => static function () use ($instance, &$random) {
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
