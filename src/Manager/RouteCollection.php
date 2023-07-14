<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Manager;

class RouteCollection
{
    /**
     * @var array
     */
    private $routeCollection = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var int[]
     */
    public $staticIndexes = [];

    /**
     * @var int[]
     */
    public $variableIndexes = [];

    /**
     * @var array
     */
    public $variableTree = [];

    /**
     * @param string[] $httpMethod
     * @param string $route
     * @param string $pattern
     * @param string[] $paramNames
     * @param bool $static
     * @param array $words
     * @param array $middlewares
     * @return $this
     */
    public function add($httpMethod, $route, $pattern, $paramNames, $static, $words, $middlewares)
    {
        $index = $static
            ? $this->addStatic($route)
            : $this->addVariable($pattern, $words);

        $this->routeCollection[$index][] = [
            'httpMethod' => $httpMethod,
            'route' => $route,
            'pattern' => $pattern,
            'paramNames' => $paramNames,
            'static' => $static,
            'middlewares' => $middlewares
        ];

        return $this;
    }

    /**
     * @param string $route
     * @return int
     */
    private function addStatic($route)
    {
        return $this->staticIndexes[$route] = isset($this->staticIndexes[$route])
            ? $this->staticIndexes[$route]
            : $this->index++;
    }

    /**
     * @param string $pattern
     * @param array $words
     * @return int
     */
    private function addVariable($pattern, $words)
    {
        $current = &$this->variableTree;
        foreach ($words as $word) {
            $current = &$current[$word];
        }

        $this->variableIndexes[$pattern] = isset($this->variableIndexes[$pattern])
            ? $this->variableIndexes[$pattern]
            : $this->index++;

        $current['$'][$this->variableIndexes[$pattern]] = $this->variableIndexes[$pattern];

        return $this->variableIndexes[$pattern];
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->index;
    }

    /**
     * @param int $index
     * @return null|array{
     *     httpMethod:string[],
     *     route:string,
     *     pattern:string,
     *     paramNames:string[],
     *     static:bool,
     *     middlewares:array
     * }[]
     */
    public function get($index)
    {
        return isset($this->routeCollection[$index]) ? $this->routeCollection[$index] : null;
    }
}
