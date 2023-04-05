<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router;

use D5WHUB\Extend\Router\Cache\Cache;
use D5WHUB\Extend\Router\Exception\RuntimeException;
use D5WHUB\Extend\Router\Exception\SyntaxException;
use D5WHUB\Extend\Router\Manager\Matcher;
use D5WHUB\Extend\Router\Manager\Parser;
use D5WHUB\Extend\Router\Manager\RouteCollection;

class Manager
{
    use Matcher;
    use Parser;

    /**
     * @var string[]
     */
    private array $friendlyCollection = [];

    /**
     * @var string[]
     */
    private array $filterCollection = [];

    private RouteCollection $routeCollection;

    public function __construct(
        private readonly Cache|null $cache = null
    ) {
        $this->routeCollection = new RouteCollection();

        $this->filterCollection['*'] = '[^\/]+'; // default
        $this->filterCollection['09'] = '[0-9]+';
        $this->filterCollection['az'] = '[a-z]+';
        $this->filterCollection['AZ'] = '[A-Z]+';
        $this->filterCollection['aZ'] = '[a-zA-Z]+';
        $this->filterCollection['d'] = '\d+';
        $this->filterCollection['D'] = '\D+';
        $this->filterCollection['w'] = '\w+';
        $this->filterCollection['W'] = '\W+';
        $this->filterCollection['uuid'] = '[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}';
    }

    /**
     * @throws SyntaxException
     */
    public function addFilter(string $key, string $pattern): void
    {
        [$key, $pattern] = $this->parseFilter($key, $pattern);

        $this->filterCollection[$key] = $pattern;
    }

    public function addFriendly(string $friendly, string $uri): void
    {
        $this->friendlyCollection[$this->parseUri($friendly)] = $this->parseUri($uri);
    }

    /**
     * @throws SyntaxException
     * @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection
     */
    public function addRoute(string|array $httpMethod, string $route, array|callable|string ...$middleware): void
    {
        $httpMethods = $this->parseHttpMethods($httpMethod);

        [$pattern, $paramNames, $static, $words] = $this->parseRoute($route);

        $this->routeCollection->add(
            $httpMethods,
            $route,
            $pattern,
            $paramNames,
            $static,
            $words,
            $middleware
        );
    }

    /**
     * @throws RuntimeException
     * @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection
     */
    public function match(string $httpMethod, string $uri): Context
    {
        $httpMethod = $this->parseHttpMethods($httpMethod, 400)[0];

        return $this->matchRoute($httpMethod, $uri);
    }
}
