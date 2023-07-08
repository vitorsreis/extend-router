<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router;

use D5WHUB\Extend\Router\Cache\CacheInterface;
use D5WHUB\Extend\Router\Exception\RuntimeException;
use D5WHUB\Extend\Router\Exception\SyntaxException;

class Router
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param CacheInterface|null $cache
     * @throws RuntimeException
     */
    public function __construct($cache = null)
    {
        if ($cache !== null && !($cache instanceof CacheInterface)) {
            throw new RuntimeException('Invalid cache instance', 500);
        }

        $this->manager = new Manager($cache);
    }

    /**
     * @param string $key
     * @param string $pattern
     * @return $this
     * @throws SyntaxException
     */
    public function addFilter($key, $pattern)
    {
        $this->manager->addFilter($key, $pattern);
        return $this;
    }

    /**
     * @param string|string[] $httpMethod
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function addRoute($httpMethod, $route, ...$middleware)
    {
        $this->manager->addRoute($httpMethod, $route, ...$middleware);
        return $this;
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function any($route, ...$middleware)
    {
        return $this->addRoute('ANY', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function get($route, ...$middleware)
    {
        return $this->addRoute('GET', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function post($route, ...$middleware)
    {
        return $this->addRoute('POST', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function put($route, ...$middleware)
    {
        return $this->addRoute('PUT', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function patch($route, ...$middleware)
    {
        return $this->addRoute('PATCH', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function delete($route, ...$middleware)
    {
        return $this->addRoute('DELETE', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function options($route, ...$middleware)
    {
        return $this->addRoute('OPTIONS', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return $this
     * @throws SyntaxException
     */
    public function head($route, ...$middleware)
    {
        return $this->addRoute('HEAD', $route, ...$middleware);
    }

    /**
     * @param string $friendly
     * @param string $route
     * @return $this
     */
    public function friendly($friendly, $route)
    {
        $this->manager->addFriendly($friendly, $route);
        return $this;
    }

    /**
     * @param string $httpMethod
     * @param string $route
     * @return Context
     * @throws RuntimeException
     */
    public function match($httpMethod, $route)
    {
        return $this->manager->match($httpMethod, $route);
    }
}
