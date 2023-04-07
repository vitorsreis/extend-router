<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

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
    private const REGEX_MATCH = '~(\*|/|:[a-zA-Z_]\w*(?:\[\w*])?|\[\w*\])~';

    private const REGEX_VARIABLE = '~:(\w+)(?:\[(\w*)])?~';

    private const REGEX_LOOSE_FILTER = '~\[(\w*)]~';

    private const PREG_SPLIT_FLAGS = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;

    private const PATTERN_FILTER_KEY = '~\W~';

    /**
     * @param string|string[] $httpMethods
     * @return string[]
     * @throws RuntimeException|SyntaxException
     */
    private function parseHttpMethods(string|array $httpMethods, int $httpCode = 500): array
    {
        if (is_string($httpMethods)) {
            $httpMethods = [$httpMethods];
        }

        return array_map(
            static fn($httpMethod) => match ($httpMethod) {
                'ANY', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD' => $httpMethod,
                default                                                           => match ($httpCode) {
                    400 => throw new RuntimeException("Http method \"$httpMethod\" invalid", $httpCode),
                    500 => throw new SyntaxException("Http method \"$httpMethod\" invalid!", $httpCode)
                }
            },
            $httpMethods
        );
    }

    /**
     * @throws SyntaxException
     */
    private function parseFilter(string $key, string $pattern): array
    {
        if (preg_match(self::PATTERN_FILTER_KEY, $key)) {
            throw new SyntaxException("Invalid key \"$key\". Use only letters, numbers and underscore.", 500);
        }

        if (!@preg_match("/$pattern/", '') && preg_last_error() !== PREG_NO_ERROR) {
            throw new SyntaxException("Invalid pattern \"/$pattern/\" on filter key \"$key\".", 500);
        }

        return [$key, $pattern];
    }

    private function parseUri(string $uri): string
    {
        do {
            $uri = preg_replace('~\*\*~', '*', $uri, -1, $count);
        } while ($count);

        do {
            $uri = preg_replace('~//~', '/', $uri, -1, $count);
        } while ($count);

        return rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';
    }

    /**
     * @throws SyntaxException
     */
    private function parseRoute(string $route): array
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

                    if (in_array($paramName, $paramNames)) {
                        throw new SyntaxException("Param with duplicate name \":$paramName\"", 500);
                    }

                    $filterKey = $matchSplit[1] ?? '*' ?: '*';
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
                    $filterKey = $matchSplit[0] ?? '';

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
                        $pattern .= preg_quote($match, '~');
                    }
                }
            }
        }

        return [$route, $pattern, $paramNames, $static, $words];
    }

    /**
     * @throws RuntimeException
     */
    private function parseMiddlewares(array $middlewares): array
    {
        return array_map(
            function ($middleware) {
                $current = $middleware['current'];
                $callback = $middleware['callback'];

                if (is_object($callback) && !is_a($callback, Closure::class)) {
                    # anonymous class
                    $callback = [$callback, '__invoke'];
                } elseif (is_string($callback)) {
                    if (str_contains($callback, '::')) {
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

                return ['current' => $current, ...$callback];
            },
            $middlewares
        );
    }

    /**
     * @throws RuntimeException
     */
    private function parseMiddlewareParams(ReflectionFunctionAbstract $reflection, bool $onlyContext = false): array
    {
        $result = [];

        foreach ($reflection->getParameters() as $param) {
            if (!$param->getType() && $param->getName() === 'context') {
                # "$context"
                $result[$param->getName()] = ['type' => 'context', 'name' => $reflection->getName()];
                continue;
            }

            if ($param->getType()) {
                if (method_exists($param->getType(), 'getTypes')) {
                    $allowed = array_map(static fn($i) => $i->getName(), $param->getType()->getTypes());
                } else {
                    $allowed = [$param->getType()->getName()];
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

            if ($param->isDefaultValueAvailable()) {
                $result[$param->getName()] = [
                    'type' => 'param',
                    'name' => $reflection->getName(),
                    'default' => $param->getDefaultValue()
                ];
            } elseif ($onlyContext) {
                throw new RuntimeException(
                    sprintf("Required argument \"%s\" for invoke \"%s\"!", $param->getName(), $reflection->getName()),
                    500
                );
            } else {
                $result[$param->getName()] = ['type' => 'param', 'name' => $reflection->getName()];
            }
        }

        return $result;
    }
}
