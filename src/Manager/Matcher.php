<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Manager;

use D5WHUB\Extend\Router\Context;
use D5WHUB\Extend\Router\Exception\RuntimeException;

trait Matcher
{
    private const INDEXES_PATTERN_MAX_CHUCK = 100;

    private const INDEXER_PATTERN_MAX_LENGTH = 10_000;

    /**
     * @throws RuntimeException
     */
    private function matchRoute(string $httpMethod, string $uri): Context
    {
        $uri = preg_quote($this->parseUri($uri), '~');
        $friendly = null;

        # REWRITE FRIENDLY
        if (isset($this->friendlyCollection[$uri])) {
            $friendly = $uri;
            $uri = $this->friendlyCollection[$uri];
        }

        $result = [];
        $resultMethodNotAllowed = false;

        $cacheKey = "match~$httpMethod:$uri";
        if ($this->cache?->has($cacheKey)) {
            $indexes = $this->cache->get($cacheKey);
            if ($indexes === 404 || $indexes === 405) {
                $resultMethodNotAllowed = $indexes === 405;
                $indexes = [];
            }
        } elseif (!$this->routeCollection->count()) {
            $indexes = [];
        } else {
            $indexes = $this->indexes($uri);
        }

        if (!empty($indexes)) {
            foreach ($indexes as $index => $paramValues) {
                $collection = array_filter(
                    $this->routeCollection->get($index),
                    static fn($i) => in_array($httpMethod, $i['httpMethod']) || in_array('ANY', $i['httpMethod'])
                );

                if (empty($collection)) {
                    $resultMethodNotAllowed = true;
                    break;
                }

                foreach ($collection as $route) {
                    $params = [];
                    foreach ($route['paramNames'] as $pos => $name) {
                        $params[$name] = $paramValues["A$pos"] ?? null;
                    }

                    $current = [
                        'route' => $index,
                        'httpMethod' => $httpMethod,
                        'uri' => $uri,
                        'friendly' => $friendly,
                        'params' => $params
                    ];

                    foreach ($route['middlewares'] as $middleware) {
                        $result[] = ['current' => $current, 'callback' => $middleware];
                    }
                }
            }
        }

        if (empty($result)) {
            $result = $resultMethodNotAllowed ? 405 : 404;
            $this->cache?->set($cacheKey, $result);
        } else {
            $this->cache?->set($cacheKey, $indexes);
        }

        return match ($result) {
            404 => throw new RuntimeException("Route \"$uri\" not found!", 404),
            405 => throw new RuntimeException("Method \"$httpMethod\" not allowed for route \"$uri\"!", 405),
            default => new Context($result)
        };
    }

    /**
     * @param string $uri
     * @return string[] index => paramValues
     */
    private function indexes(string $uri): array
    {
        $result = [];

        $this->indexesStatic($uri, $result);
        $this->indexesVariable($uri, $result);

        return $result;
    }

    private function indexesStatic(string $uri, array &$result): void
    {
        if (isset($this->routeCollection->staticIndexes[$uri])) {
            $result[$this->routeCollection->staticIndexes[$uri]] = [];
        }
    }

    private function indexesVariable(string $uri, array &$result): void
    {
        if (empty($this->routeCollection->variableIndexes)) {
            return;
        }

        $indexes = $this->indexesWord(
            $this->routeCollection->variableWords,
            preg_split('~(/)~', $uri, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) ?: [],
            false,
            false
        );

        if (empty($indexes)) {
            return;
        }

        $this->indexesMasked($uri, $result, $indexes);
    }

    private function indexesWord(array $candidates, array $words, bool $is_joker, bool $is_variable): array
    {
        if (empty($candidates)) {
            return [];
        }

        if (empty($words)) {
            return $candidates['$'] ?? [];
        }

        $word = array_shift($words);

        $indexes = [];

        if (!empty($candidates['*']) && $match = $this->indexesWord(
            $candidates['*'],
            $words,
            true,
            false
        )) {
            $indexes = [...$indexes, ...$match];
        }

        if ($word !== '/' && !empty($candidates[':']) && $match = $this->indexesWord(
            $candidates[':'],
            $words,
            false,
            true
        )) {
            $indexes = [...$indexes, ...$match];
        }

        if ($word !== '/' && $is_variable && $match = $this->indexesWord(
            $candidates,
            $words,
            false,
            true
        )) {
            $indexes = [...$indexes, ...$match];
        }

        if ($is_joker && $match = $this->indexesWord(
            $candidates,
            $words,
            true,
            false
        )) {
            $indexes = [...$indexes, ...$match];
        }

        if (!empty($candidates[$word]) && $match = $this->indexesWord(
            $candidates[$word],
            $words,
            false,
            false
        )) {
            $indexes = [...$indexes, ...$match];
        }

        return $indexes;
    }

    private function indexesMasked(string $uri, array &$result, array $indexes): void
    {
        $cursor = 0;
        $total = count($indexes);

        while ($cursor < $total) {
            $pattern = '';
            $length = 0;

            for ($chunk = 0; $chunk < self::INDEXES_PATTERN_MAX_CHUCK && $cursor < $total; $chunk++, $cursor++) {
                $append = "|{$this->routeCollection->get($indexes[$cursor])[0]['pattern']}(*MARK:$cursor)";
                $appendLength = strlen($append);

                if ($length + $appendLength > self::INDEXER_PATTERN_MAX_LENGTH) {
                    break;
                }

                $pattern .= $append;
                $length += $appendLength;
            }

            if (preg_match("~^(?$pattern)$~", $uri, $match)) {
                $result[$indexes[$match['MARK']]] = array_slice($match, 1, -1);
                $cursor = (int)$match['MARK'] + 1;
            }
        }
    }
}
