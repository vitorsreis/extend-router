<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

/** @var array $setting */
/** @var D5WHUB\Extend\Benchmark\Benchmark\Collection[] $benchmark */

(static function ($setting, $benchmark) {
    $title = 'Symfony Routing';
    $argument = $setting['arguments']['{arg}'];

    $context = new Symfony\Component\Routing\RequestContext();
    $instance = static function () use ($setting, $argument, $context) {
        $collection = new Symfony\Component\Routing\RouteCollection();

        for ($i = 0, $cursor = 0; $i < $setting['num_routes']; $i++, $cursor++) {
            [ $route, $url ] = $argument['routes'][$i];
            if (!$i || $i === $setting['num_routes'] - 1 || in_array($url, $argument['match']['rand'])) {
                $method = ['GET'];
            } else {
                $method = match ($cursor) {
                    1       => ['GET'],
                    2       => ['POST'],
                    3       => ['PUT'],
                    4       => ['PATCH'],
                    5       => ['DELETE'],
                    6       => ['OPTIONS'],
                    7       => ['HEAD'],
                    default => []
                };
                $cursor = $cursor > 7 ? 0 : $cursor;
            }

            $collection->add((string)$i, new Symfony\Component\Routing\Route(
                $route,
                ['_controller' => static fn() => 'TEST'],
                [],
                [],
                '',
                [],
                $method,
                ''
            ));
        }

        return new Symfony\Component\Routing\Matcher\UrlMatcher(
            $collection,
            $context
        );
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
        (static fn ($instance) => static function () use ($context, $instance, $argument)
		{
            $context->setMethod('GET');
            return $instance->match($argument['match']['first'])['_controller']();
        })($instance())
    );

    ($benchmark['last'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn ($instance) => static function () use ($context, $instance, $argument)
		{
            $context->setMethod('GET');
            return $instance->match($argument['match']['last'])['_controller']();
        })($instance())
    );

    ($benchmark['not-found'] ?? null)?->addTest(
        $title,
        ['throw' => Symfony\Component\Routing\Exception\ResourceNotFoundException::class],
        (static fn ($instance) => static function () use ($context, $instance)
		{
            $context->setMethod('GET');
            $instance->match('/not-even-real');
        })($instance())
    );

    ($benchmark['first-not-allowed'] ?? null)?->addTest(
        $title,
        ['throw' => Symfony\Component\Routing\Exception\MethodNotAllowedException::class],
        (static fn ($instance) => static function () use ($context, $instance, $argument)
		{
            $context->setMethod('POST');
            $instance->match($argument['match']['first']);
        })($instance())
    );

    ($benchmark['last-not-allowed'] ?? null)?->addTest(
        $title,
        ['throw' => Symfony\Component\Routing\Exception\MethodNotAllowedException::class],
        (static fn ($instance) => static function () use ($context, $instance, $argument)
		{
            $context->setMethod('POST');
            $instance->match($argument['match']['last']);
        })($instance())
    );

    $random = $argument['match']['rand'];
    ($benchmark['rand'] ?? null)?->addTest(
        $title,
        ['return' => 'TEST'],
        (static fn ($instance) => static function () use ($context, $instance, &$random)
		{
            $context->setMethod('GET');
            return $instance->match(array_shift($random))['_controller']();
        })($instance())
    );
})($setting, $benchmark);
