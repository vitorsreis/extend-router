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

class Router
{
    private Manager $manager;

    public function __construct(Cache|null $cache = null)
    {
        $this->manager = new Manager($cache);
    }

    /**
     * @throws SyntaxException
     */
    public function addFilter(string $key, string $pattern): self
    {
        $this->manager->addFilter($key, $pattern);
        return $this;
    }

    /**
     * @param string|string[] $httpMethod
     * @throws SyntaxException
     */
    public function addRoute(string|array $httpMethod, string $route, string|callable|array ...$middleware): self
    {
        $this->manager->addRoute($httpMethod, $route, ...$middleware);
        return $this;
    }

    /**
     * @throws SyntaxException
     */
    public function any(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('ANY', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function get(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('GET', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function post(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('POST', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function put(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('PUT', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function patch(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('PATCH', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function delete(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('DELETE', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function options(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('OPTIONS', $route, ...$middleware);
    }

    /**
     * @throws SyntaxException
     */
    public function head(string $route, string|callable|array ...$middleware): self
    {
        return $this->addRoute('HEAD', $route, ...$middleware);
    }

    public function friendly(string $friendly, string $route): self
    {
        $this->manager->addFriendly($friendly, $route);
        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function match(string $httpMethod, string $route): Context|false
    {
        return $this->manager->match($httpMethod, $route);
    }
}
