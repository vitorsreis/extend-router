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
        $this->manager->addRoute($httpMethod, $this->group . $route, ...$middleware);
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
     * @param string $route
     * @param callable $callback function(Router $router)
     * @return $this
     * @throws RuntimeException
     */
    public function group($route, $callback)
    {
        call_user_func_array(
            $callback,
            [new self(null, $this->group . $route, $this->manager)]
        );
        return $this;
    }

    /**
     * @param string $httpMethod
     * @param string $route
     * @return Context
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function match($httpMethod, $route)
    {
        return $this->manager->match($httpMethod, $route);
    }
}
