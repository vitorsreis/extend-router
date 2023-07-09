<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router;

use D5WHUB\Extend\Router\Cache\CacheInterface;
use D5WHUB\Extend\Router\Exception\RuntimeException;
use D5WHUB\Extend\Router\Exception\SyntaxException;
use D5WHUB\Extend\Router\Manager\Constants;
use D5WHUB\Extend\Router\Manager\Matcher;
use D5WHUB\Extend\Router\Manager\Parser;
use D5WHUB\Extend\Router\Manager\RouteCollection;

class Manager extends Constants
{
    use Matcher;
    use Parser;

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var array<string, string>
     */
    private $friendlyCollection = [];

    /**
     * @var array<string, string>
     */
    private $filterCollection = [];

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @param CacheInterface|null $cache
     */
    public function __construct($cache = null)
    {
        $this->cache = $cache;
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
     * @param string $key
     * @param string $pattern
     * @return void
     * @throws SyntaxException
     */
    public function addFilter($key, $pattern)
    {
        list($key, $pattern) = $this->parseFilter($key, $pattern);
        $this->filterCollection[$key] = $pattern;
    }

    /**
     * @param string $friendly
     * @param string $uri
     * @return void
     */
    public function addFriendly($friendly, $uri)
    {
        $this->friendlyCollection[$this->parseUri($friendly)] = $this->parseUri($uri);
    }

    /**
     * @param array|string $httpMethod
     * @param string $route
     * @param array|callable|string ...$middleware
     * @return void
     * @throws SyntaxException
     * @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection
     */
    public function addRoute($httpMethod, $route, ...$middleware)
    {
        $httpMethods = $this->parseHttpMethods($httpMethod);
        list($route, $pattern, $paramNames, $static, $words) = $this->parseRoute($route);

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
     * @param string $httpMethod
     * @param string $uri
     * @return Context
     * @throws RuntimeException
     * @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection
     */
    public function match($httpMethod, $uri)
    {
        $httpMethod = $this->parseHttpMethods($httpMethod, 400)[0];
        return $this->matchRoute($httpMethod, $uri);
    }
}
