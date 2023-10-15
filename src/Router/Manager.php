<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router;

use VSR\Extend\Router\Cache\CacheInterface;
use VSR\Extend\Router\Exception\MethodNotAllowedException;
use VSR\Extend\Router\Exception\NotFoundException;
use VSR\Extend\Router\Exception\RuntimeException;
use VSR\Extend\Router\Exception\SyntaxException;
use VSR\Extend\Router\Manager\Constants;
use VSR\Extend\Router\Manager\Matcher;
use VSR\Extend\Router\Manager\Parser;
use VSR\Extend\Router\Manager\RouteCollection;

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
     * @var string[]
     */
    public $methodCollection = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

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
     * @param array|string $method
     * @param string $route
     * @param array|callable|string ...$middleware
     * @return void
     * @throws SyntaxException
     * @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection
     */
    public function addRoute($method, $route, ...$middleware)
    {
        $methods = $this->parseMethods($method);
        list($route, $pattern, $paramNames, $static, $words) = $this->parseRoute($route);

        $this->routeCollection->add(
            $methods,
            $route,
            $pattern,
            $paramNames,
            $static,
            $words,
            $middleware
        );
    }

    /**
     * @param CacheInterface|null $cache
     * @throws RuntimeException
     */
    public function setCache($cache)
    {
        if ($cache !== null && !($cache instanceof CacheInterface)) {
            throw new RuntimeException('Invalid cache instance', 500);
        }

        $this->cache = $cache;
    }

    /**
     * @param string ...$method
     * @return void
     */
    public function allowMethod(...$method)
    {
        $this->methodCollection = array_unique(array_merge($this->methodCollection, $method));
    }

    /**
     * @param string ...$method
     * @return void
     */
    public function disallowMethod(...$method)
    {
        $this->methodCollection = array_diff($this->methodCollection, $method);
    }

    /**
     * @param string $method
     * @param string $uri
     * @return Context
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RuntimeException
     * @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection
     */
    public function match($method, $uri)
    {
        $method = $this->parseMethods($method, 400)[0];
        return $this->matchRoute($method, $uri);
    }
}
