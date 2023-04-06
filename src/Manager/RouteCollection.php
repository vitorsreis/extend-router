<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Manager;

class RouteCollection
{
    private array $routeCollection = [];

    private int $index = 0;

    /**
     * @var int[]
     */
    public array $staticIndexes = [];

    /**
     * @var int[]
     */
    public array $variableIndexes = [];

    public array $variableWords = [];

    /**
     * @param string[] $httpMethod
     * @param string[] $paramNames
     */
    public function add(
        array  $httpMethod,
        string $route,
        string $pattern,
        array  $paramNames,
        bool   $static,
        array  $words,
        array  $middlewares
    ): self {
        $index = $static
            ? $this->addStatic($pattern)
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

    private function addStatic(string $route): int
    {
        return $this->staticIndexes[$route] = $this->staticIndexes[$route] ?? $this->index++;
    }

    private function addVariable(string $pattern, array $words): int
    {
        $current = &$this->variableWords;
        $total = count($words);
        for ($i = 0; $i < $total; $i++) {
            $current = &$current[$words[$i]];
        }

        $this->variableIndexes[$pattern] = $this->variableIndexes[$pattern] ?? $this->index++;

        $current['$'][] = $this->variableIndexes[$pattern];

        return $this->variableIndexes[$pattern];
    }

    public function count(): int
    {
        return $this->index;
    }

    /**
     * @return null|array{
     *     httpMethod:string[],
     *     route:string,
     *     pattern:string,
     *     paramNames:string[],
     *     static:bool,
     *     middlewares:array
     * }[]
     */
    public function get(int $index): array|null
    {
        return $this->routeCollection[$index] ?? null;
    }
}
