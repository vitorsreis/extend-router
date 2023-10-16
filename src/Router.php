<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend;

use VSR\Extend\Router\Cache\CacheInterface;
use VSR\Extend\Router\Context;
use VSR\Extend\Router\Exception\MethodNotAllowedException;
use VSR\Extend\Router\Exception\NotFoundException;
use VSR\Extend\Router\Exception\RuntimeException;
use VSR\Extend\Router\Exception\SyntaxException;
use VSR\Extend\Router\Manager;

class Router
{
    /**
     * @var string|null
     */
    private $group = '';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param string $group
     * @param CacheInterface|null $cache
     * @param Manager|null $manager
     * @throws RuntimeException
     */
    public function __construct($cache = null, $group = null, $manager = null)
    {
        if ($cache !== null && !($cache instanceof CacheInterface)) {
            throw new RuntimeException('Invalid cache instance', 500);
        }

        if ($manager !== null && !($manager instanceof Manager)) {
            throw new RuntimeException('Invalid manager instance', 500);
        }

        $this->manager = $manager ?: new Manager($cache);
        $this->group = $group ?: '';
    }

    /**
     * @param string $key
     * @param string $pattern
     * @return static
     * @throws SyntaxException
     */
    public function addFilter($key, $pattern)
    {
        $this->manager->addFilter($key, $pattern);
        return $this;
    }

    /**
     * @param string|string[] $method
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function addRoute($method, $route, ...$middleware)
    {
        $this->manager->addRoute($method, $this->group . $route, ...$middleware);
        return $this;
    }

    /**
     * @param CacheInterface|null $cache
     * @throws RuntimeException
     */
    public function setCache($cache)
    {
        $this->manager->setCache($cache);
    }

    /**
     * @param string ...$method
     * @return static
     */
    public function allowMethod(...$method)
    {
        $this->manager->allowMethod(...$method);
        return $this;
    }

    /**
     * @param string ...$method
     * @return static
     */
    public function disallowMethod(...$method)
    {
        $this->manager->disallowMethod(...$method);
        return $this;
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function any($route, ...$middleware)
    {
        return $this->addRoute('ANY', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function get($route, ...$middleware)
    {
        return $this->addRoute('GET', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function post($route, ...$middleware)
    {
        return $this->addRoute('POST', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function put($route, ...$middleware)
    {
        return $this->addRoute('PUT', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function patch($route, ...$middleware)
    {
        return $this->addRoute('PATCH', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function delete($route, ...$middleware)
    {
        return $this->addRoute('DELETE', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function options($route, ...$middleware)
    {
        return $this->addRoute('OPTIONS', $route, ...$middleware);
    }

    /**
     * @param string $route
     * @param callable|array|string ...$middleware
     * @return static
     * @throws SyntaxException
     */
    public function head($route, ...$middleware)
    {
        return $this->addRoute('HEAD', $route, ...$middleware);
    }

    /**
     * @param string $friendly
     * @param string $route
     * @return static
     */
    public function friendly($friendly, $route)
    {
        $this->manager->addFriendly($friendly, $route);
        return $this;
    }

    /**
     * @param string $route
     * @param callable|array|string|null $callback function(Router $router)
     * @return static
     * @throws RuntimeException
     */
    public function group($route, $callback = null)
    {
        $instance = new static(null, $this->group . $route, $this->manager);
        if (null === $callback) {
            return $instance;
        }

        (new Caller($callback))->execute([$instance]);
        return $this;
    }

    /**
     * @param string $method
     * @param string $route
     * @return Context
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function match($method, $route)
    {
        return $this->manager->match($method, $route);
    }
}
