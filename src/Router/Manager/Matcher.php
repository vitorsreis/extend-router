<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Manager;

use VSR\Extend\Router\Context;
use VSR\Extend\Router\Exception\MethodNotAllowedException;
use VSR\Extend\Router\Exception\NotFoundException;
use VSR\Extend\Router\Exception\RuntimeException;

trait Matcher
{
    /**
     * @param string $httpMethod
     * @param string $uri
     * @return Context
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    private function matchRoute($httpMethod, $uri)
    {
        $uri = $this->parseUri($uri);
        $friendly = null;

        # REWRITE FRIENDLY
        if (array_key_exists($uri, $this->friendlyCollection)) {
            $friendly = $uri;
            $uri = $this->friendlyCollection[$uri];
        }

        $result = [];
        $resultMethodNotAllowed = false;

        $cacheKey = "match-" . sha1("$httpMethod:$uri");
        if (!empty($this->cache) && $this->cache->has($cacheKey)) {
            $cache = $this->cache->get($cacheKey);
            if ($cache == '404' || $cache == '405') {
                $resultMethodNotAllowed = $cache == '405';
                $indexes = [];
            } else {
                $indexes = $cache;
            }
        } elseif (!$this->routeCollection->count()) {
            $indexes = [];
        } else {
            $indexes = $this->indexes($uri);
        }

        $allowedMethods = [];

        if (!empty($indexes)) {
            foreach ($indexes as $index => $paramValues) {
                $collection = array_filter(
                    $this->routeCollection->get($index),
                    static function ($i) use ($httpMethod, &$allowedMethods) {
                        $allowedMethods = array_merge($allowedMethods, $i['httpMethod']);
                        return in_array($httpMethod, $i['httpMethod']) || in_array('ANY', $i['httpMethod']);
                    }
                );

                if (empty($collection)) {
                    $resultMethodNotAllowed = true;
                    break;
                }

                foreach ($collection as $order => $route) {
                    $params = [];
                    foreach ($route['paramNames'] as $pos => $name) {
                        $params[$name] = isset($paramValues["A$pos"]) ? $paramValues["A$pos"] : null;
                    }

                    $current = [
                        'route' => $index,
                        'httpMethod' => $httpMethod,
                        'uri' => $uri,
                        'friendly' => $friendly,
                        'params' => $params
                    ];

                    foreach ($route['middlewares'] as $suborder => $middleware) {
                        $result["$order-$suborder"] = ['current' => $current, 'callback' => $middleware];
                    }
                }
            }
        }

        $allowedMethods = array_unique($allowedMethods);

        if (empty($result)) {
            $result = $resultMethodNotAllowed ? '405' : '404';
            empty($this->cache) ?: $this->cache->set($cacheKey, $result);
        } else {
            empty($this->cache) ?: $this->cache->set($cacheKey, $indexes);

            uksort($result, function ($a, $b) {
                $a = explode('-', $a);
                $b = explode('-', $b);
                return $a === $b ? 0 : ($a[0] !== $b[0] ? ($a[0] > $b[0] ? 1 : -1) : ($a[1] > $b[1] ? 1 : -1));
            });

            $result = array_values($result);
        }

        switch ($result) {
            case '404':
                throw new NotFoundException("Route \"$uri\" not found", 404);
            case '405':
                throw new MethodNotAllowedException(
                    "Method \"$httpMethod\" not allowed for route \"$uri\"",
                    405,
                    $allowedMethods
                );
            default:
                $context = new Context($result, $allowedMethods, $this->cache);
                $context->cached = isset($cache);
                return $context;
        }
    }

    /**
     * @param string $uri
     * @return string[] index => paramValues
     */
    private function indexes($uri)
    {
        $result = [];

        $this->indexesStatic($uri, $result);
        $this->indexesVariable($uri, $result);

        return $result;
    }

    /**
     * @param string $uri
     * @param array &$result
     * @return void
     */
    private function indexesStatic($uri, &$result)
    {
        if (isset($this->routeCollection->staticIndexes[$uri])) {
            $result[$this->routeCollection->staticIndexes[$uri]] = [];
        }
    }

    /**
     * @param string $uri
     * @param array &$result
     * @return void
     */
    private function indexesVariable($uri, &$result)
    {
        if (empty($this->routeCollection->variableIndexes)) {
            return;
        }

        $indexes = $this->indexesTree(
            $this->routeCollection->variableTree,
            preg_split(
                self::REGEX_DELIMITER . '(/)' . self::REGEX_DELIMITER,
                $uri,
                -1,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            ) ?: [],
            false,
            false
        );

        if (empty($indexes)) {
            return;
        }

        $this->indexesMasked($uri, $result, $indexes);
    }

    /**
     * @param array $candidates
     * @param array $words
     * @param bool $is_joker
     * @param bool $is_variable
     * @return array
     */
    private function indexesTree($candidates, $words, $is_joker, $is_variable)
    {
        if (empty($candidates)) {
            return [];
        }

        if (empty($words)) {
            return isset($candidates['$']) ? $candidates['$'] : [];
        }

        $word = array_shift($words);

        $indexes = [];

        if (
            !empty($candidates['*']) && $match = $this->indexesTree(
                $candidates['*'],
                $words,
                true,
                false
            )
        ) {
            $indexes = array_merge($indexes, $match);
        }

        if (
            $word !== '/' && !empty($candidates[':']) && $match = $this->indexesTree(
                $candidates[':'],
                $words,
                false,
                true
            )
        ) {
            $indexes = array_merge($indexes, $match);
        }

        if (
            $word !== '/' && $is_variable && $match = $this->indexesTree(
                $candidates,
                $words,
                false,
                true
            )
        ) {
            $indexes = array_merge($indexes, $match);
        }

        if (
            $is_joker && $match = $this->indexesTree(
                $candidates,
                $words,
                true,
                false
            )
        ) {
            $indexes = array_merge($indexes, $match);
        }

        if (
            !empty($candidates[$word]) && $match = $this->indexesTree(
                $candidates[$word],
                $words,
                false,
                false
            )
        ) {
            $indexes = array_merge($indexes, $match);
        }

        return $indexes;
    }

    /**
     * @param string $uri
     * @param array &$result
     * @param array $indexes
     * @return void
     */
    private function indexesMasked($uri, &$result, $indexes)
    {
        $cursor = 0;
        $total = count($indexes);

        while ($cursor < $total) {
            $pattern = '';
            $length = 0;

            for ($chunk = 0; $chunk < self::INDEXES_PATTERN_MAX_CHUCK && $cursor < $total; $chunk++, $cursor++) {
                $append = "|" . current($this->routeCollection->get($indexes[$cursor]))['pattern'] . "(*MARK:$cursor)";
                $appendLength = strlen($append);

                if ($length + $appendLength > self::INDEXER_PATTERN_MAX_LENGTH) {
                    break;
                }

                $pattern .= $append;
                $length += $appendLength;
            }

            if (preg_match(self::REGEX_DELIMITER . "^(?$pattern)$" . self::REGEX_DELIMITER, $uri, $match)) {
                $result[$indexes[$match['MARK']]] = array_slice($match, 1, -1);
                $cursor = (int)$match['MARK'] + 1;
            }
        }
    }
}
