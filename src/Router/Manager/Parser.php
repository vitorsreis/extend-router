<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
 */

namespace VSR\Extend\Router\Manager;

use Exception;
use Throwable;
use VSR\Extend\Caller\Parser as CallerParser;
use VSR\Extend\Router\Exception\RuntimeException;
use VSR\Extend\Router\Exception\SyntaxException;

trait Parser
{
    use CallerParser;

    /**
     * @param string|string[] $method
     * @param int $httpCode
     * @return string[]
     * @throws RuntimeException
     * @throws SyntaxException
     */
    private function parseMethods($method, $httpCode = 500)
    {
        if (is_string($method)) {
            $method = [$method];
        }

        return array_map(
            function ($method) use ($httpCode) {
                if ($method !== 'ANY' && !in_array($method, $this->methodCollection, true)) {
                    throw $httpCode === 400
                        ? new RuntimeException("Method \"$method\" invalid", $httpCode)
                        : new SyntaxException("Method \"$method\" invalid", $httpCode);
                }

                return $method;
            },
            $method
        );
    }

    /**
     * @param string $key
     * @param string $pattern
     * @return array{string, string}
     * @throws SyntaxException
     */
    private function parseFilter($key, $pattern)
    {
        if (preg_match(self::REGEX_FILTER_KEY, $key)) {
            throw new SyntaxException("Invalid key \"$key\". Use only letters, numbers and underscore", 500);
        }

        $try = self::REGEX_DELIMITER . $pattern . self::REGEX_DELIMITER;
        if (!@preg_match($try, '') && preg_last_error() !== PREG_NO_ERROR) {
            throw new SyntaxException("Invalid pattern \"$try\" on filter key \"$key\"", 500);
        }

        return [$key, $pattern];
    }

    /**
     * @param string $uri
     * @return string
     */
    private function parseUri($uri)
    {
        if (!$uri) {
            return '/';
        }

        do {
            $uri = preg_replace(self::REGEX_DELIMITER . '\*\*' . self::REGEX_DELIMITER, '*', $uri, -1, $count);
        } while ($count);

        do {
            $uri = preg_replace(self::REGEX_DELIMITER . '//' . self::REGEX_DELIMITER, '/', $uri, -1, $count);
        } while ($count);

        return rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';
    }

    /**
     * @param string $route
     * @return array{string, string, string[], string[], string[]}
     * @throws SyntaxException
     */
    private function parseRoute($route)
    {
        $route = $this->parseUri($route);

        $static = true;
        $pattern = '';
        $words = [];
        $paramNames = [];
        $argCount = 0;

        if ($split = preg_split(self::REGEX_MATCH, $route, -1, self::PREG_SPLIT_FLAGS)) {
            foreach ($split as $match) {
                if ($match[0] === ':') {
                    $words[] = ':';

                    $matchSplit = preg_split(self::REGEX_VARIABLE, $match, 2, self::PREG_SPLIT_FLAGS);
                    $paramName = $matchSplit[0];

                    if (strcasecmp($paramName, 'context') == 0) {
                        throw new SyntaxException("Param with reserved name \":$paramName\"", 500);
                    }

                    if (in_array($paramName, $paramNames)) {
                        throw new SyntaxException("Param with duplicate name \":$paramName\"", 500);
                    }

                    $filterKey = (isset($matchSplit[1]) ? $matchSplit[1] : '*') ?: '*';
                    if (!isset($this->filterCollection[$filterKey])) {
                        throw new SyntaxException("Filter \"$filterKey\" not implemented", 500);
                    }

                    $static = false;
                    $pattern .= "(?<A$argCount>" . $this->filterCollection[$filterKey] . ")";
                    $paramNames[$argCount] = $paramName;
                    $argCount++;
                } elseif ($match[0] === '[') {
                    $words[] = '*';

                    $matchSplit = preg_split(self::REGEX_LOOSE_FILTER, $match, 2, self::PREG_SPLIT_FLAGS);
                    $filterKey = isset($matchSplit[0]) ? $matchSplit[0] : '';

                    if (!isset($this->filterCollection[$filterKey])) {
                        throw new SyntaxException("Filter \"$filterKey\" not implemented", 500);
                    }

                    $static = false;
                    $pattern .= $this->filterCollection[$filterKey];
                } else {
                    $words[] = $match;

                    if ($match[0] === '*') {
                        $static = false;
                        $pattern .= '.*?';
                    } elseif ($match[0] === '/') {
                        $pattern .= '\/';
                    } else {
                        $pattern .= preg_quote($match, self::REGEX_DELIMITER);
                    }
                }
            }
        }

        return [$route, $pattern, $paramNames, $static, $words];
    }

    /**
     * @param array $middlewares
     * @return array<int, array{
     *     current: string,
     *     callback: callable,
     *     params: array<string, mixed>,
     *     construct: array<string, mixed>
     * }>
     * @throws RuntimeException
     */
    private function parseMiddlewares(array $middlewares)
    {
        return array_map(
            function ($middleware) {
                try {
                    return ['current' => $middleware['current']] + self::parseMiddleware($middleware['callback']);
                } catch (Exception $e) {
                    throw new RuntimeException($e->getMessage(), 500, $e);
                } catch (Throwable $e) {
                    throw new RuntimeException($e->getMessage(), 500, $e);
                }
            },
            $middlewares
        );
    }
}
