<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router;

use D5WHUB\Extend\Router\Cache\CacheInterface;
use D5WHUB\Extend\Router\Context\Caller;
use D5WHUB\Extend\Router\Context\Current;
use D5WHUB\Extend\Router\Context\Header;
use D5WHUB\Extend\Router\Context\Header\ContextState;
use D5WHUB\Extend\Router\Exception\RuntimeException;
use D5WHUB\Extend\Router\Manager\Parser;

class Context
{
    use Caller;
    use Parser;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Current
     */
    public $current;

    /**
     * @var Header
     */
    public $header;

    /**
     * @var mixed Partial result
     */
    public $result = null;

    /**
     * @var bool Result is cached
     */
    public $cached = false;

    /**
     * @var mixed Persist execution data
     */
    private $data = [];

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @throws RuntimeException
     */
    public function __construct(array $middlewares, CacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->current = new Current();
        $this->header = new Header();
        $this->middlewares = $this->parseMiddlewares($middlewares);
        $this->header->hash = sha1(serialize(array_column($this->middlewares, 'current')));
        $this->header->total = count($this->middlewares);
    }

    /**
     * @param callable|array|string|null $callback
     * @return $this
     * @throws RuntimeException
     */
    public function execute($callback = null)
    {
        if ($callback !== null) {
            $callback = $this->parseMiddlewares([['current' => null, 'callback' => $callback]])[0];
        }

        if ($this->header->state !== ContextState::PENDING || !count($this->middlewares)) {
            $this->cached = true;
            return $this;
        }

        $this->header->state = ContextState::RUNNING;
        $this->header->cursor = 1;
        $this->header->startTime = microtime(true);
        $this->header->endTime = null;
        $this->header->elapsedTime = null;
        $this->cached = false;

        $cacheKey = "execute-{$this->header->hash}";
        if (!empty($this->cache) && $this->cache->has($cacheKey)) {
            $this->cached = true;
            $this->result = $this->cache->get($cacheKey);
        } else {
            for (; $this->header->cursor <= $this->header->total; $this->header->cursor++) {
                $middleware = $this->middlewares[$this->header->cursor - 1];

                $this->current->route = $middleware['current']['route'];
                $this->current->httpMethod = $middleware['current']['httpMethod'];
                $this->current->uri = $middleware['current']['uri'];
                $this->current->friendly = $middleware['current']['friendly'];
                $this->current->params = (object)$middleware['current']['params'];

                $this->result = $this->call($middleware['callable'], $middleware['params'], $middleware['construct']);
                if ($callback !== null) {
                    $this->call($callback['callable'], [['type' => 'context']], $callback['construct']);
                }

                if ($this->header->state !== ContextState::RUNNING) {
                    break;
                }
            }
        }

        if ($this->header->state === ContextState::RUNNING) {
            $this->header->state = ContextState::COMPLETED;
        }
        $this->header->endTime = microtime(true);
        $this->header->elapsedTime = $this->header->endTime - $this->header->startTime;
        empty($this->cache) ?: $this->cache->set($cacheKey, $this->result);

        return $this;
    }

    /**
     * @return $this
     */
    public function stop()
    {
        $this->header->state = ContextState::STOPPED;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }
}
