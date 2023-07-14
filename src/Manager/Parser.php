<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Manager;

use Closure;
use D5WHUB\Extend\Router\Context;
use D5WHUB\Extend\Router\Exception\RuntimeException;
use D5WHUB\Extend\Router\Exception\SyntaxException;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

trait Parser
{
    /**
     * @param string|string[] $httpMethods
     * @param int $httpCode
     * @return string[]
     * @throws RuntimeException
     * @throws SyntaxException
     */
    private function parseHttpMethods($httpMethods, $httpCode = 500)
    {
        if (is_string($httpMethods)) {
            $httpMethods = [$httpMethods];
        }

        return array_map(
            static function ($httpMethod) use ($httpCode) {
                switch ($httpMethod) {
                    case 'ANY':
                    case 'GET':
                    case 'POST':
                    case 'PUT':
                    case 'PATCH':
                    case 'DELETE':
                    case 'OPTIONS':
                    case 'HEAD':
                        return $httpMethod;

                    default:
                        throw $httpCode === 400
                            ? new RuntimeException("Http method \"$httpMethod\" invalid", $httpCode)
                            : new SyntaxException("Http method \"$httpMethod\" invalid", $httpCode);
                }
            },
            $httpMethods
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

        $try = self::REGEX_DELIMITER . preg_quote($pattern, self::REGEX_DELIMITER) . self::REGEX_DELIMITER;
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
                    $pattern .= "(?<A$argCount>{$this->filterCollection[$filterKey]})";
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
     * @return array{
     *     current: string,
     *     callback: callable,
     *     params: array<string, mixed>,
     *     construct: array<string, mixed>
     * }[]
     * @throws RuntimeException
     */
    private function parseMiddlewares(array $middlewares)
    {
        return array_map(
            function ($middleware) {
                $current = $middleware['current'];
                $callback = $middleware['callback'];

                if (is_object($callback) && !is_a($callback, Closure::class)) {
                    # anonymous class
                    $callback = [$callback, '__invoke'];
                } elseif (is_string($callback)) {
                    if (strpos($callback, '::') !== false) {
                        # class::method string
                        $callback = explode('::', $callback);
                    } elseif (!function_exists($callback) && class_exists($callback)) {
                        # class::__invoke
                        $callback = [$callback, '__invoke'];
                    }
                }

                if (is_array($callback)) {
                    # method / static method
                    try {
                        $reflection = new ReflectionMethod($callback[0], $callback[1]);
                    } catch (ReflectionException $e) {
                        throw new RuntimeException($e->getMessage(), 500, $e);
                    }

                    if (!$reflection->isStatic()) {
                        $construct = method_exists($callback[0], '__construct')
                            ? $this->parseMiddlewareParams(new ReflectionMethod($callback[0], '__construct'), true)
                            : [];

                        $callback = [
                            'callable' => $callback,
                            'params' => $this->parseMiddlewareParams($reflection),
                            'construct' => $construct
                        ];
                    } else {
                        $callback = [
                            'callable' => $callback,
                            'params' => $this->parseMiddlewareParams($reflection),
                            'construct' => null
                        ];
                    }
                } else {
                    # function / anonymous function / arrow function / string function
                    try {
                        $reflection = new ReflectionFunction($callback);
                        $callback = [
                            'callable' => $callback,
                            'params' => $this->parseMiddlewareParams($reflection),
                            'construct' => null
                        ];
                    } catch (ReflectionException $e) {
                        throw new RuntimeException($e->getMessage(), 500, $e);
                    }
                }

                return ['current' => $current] + $callback;
            },
            $middlewares
        );
    }

    /**
     * @param ReflectionFunctionAbstract $reflection
     * @param bool $onlyContext
     * @return array
     * @throws RuntimeException
     */
    private function parseMiddlewareParams($reflection, $onlyContext = false)
    {
        $result = [];

        foreach ($reflection->getParameters() as $param) {
            $paramType = method_exists($param, 'getType') ? $param->getType() : $param->getClass();
            if (!$paramType && $param->getName() === 'context') {
                # "$context"
                $result[$param->getName()] = ['type' => 'context', 'name' => $reflection->getName()];
                continue;
            }

            if ($paramType) {
                if (method_exists($paramType, 'getTypes')) {
                    $allowed = array_map(static function ($i) {
                        return $i->getName();
                    }, $paramType->getTypes());
                } elseif (method_exists($paramType, 'getName')) {
                    $allowed = [$paramType->getName()];
                } else {
                    $allowed = ['mixed'];
                }

                if (in_array('mixed', $allowed)) {
                    # "mixed $context"
                    $result[$param->getName()] = ['type' => 'context', 'name' => $reflection->getName()];
                    continue;
                }

                if (in_array(Context::class, $allowed)) {
                    # "Context $..."
                    $result[$param->getName()] = ['type' => 'context', 'name' => $reflection->getName()];
                    continue;
                }
            }

            if ($param->isOptional()) {
                $result[$param->getName()] = [
                    'type' => 'param',
                    'name' => $reflection->getName(),
                    'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
                ];
            } elseif ($onlyContext) {
                throw new RuntimeException(
                    sprintf("Required argument \"%s\" for invoke \"%s\"", $param->getName(), $reflection->getName()),
                    500
                );
            } else {
                $result[$param->getName()] = ['type' => 'param', 'name' => $reflection->getName()];
            }
        }

        return $result;
    }
}
