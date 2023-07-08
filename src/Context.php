<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router;

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
     * @var mixed Persist execution data
     */
    private $data = null;

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @throws RuntimeException
     */
    public function __construct(array $middlewares)
    {
        $this->current = new Current();
        $this->header = new Header();
        $this->middlewares = $this->parseMiddlewares($middlewares);
        $this->header->total = count($this->middlewares);
    }

    /**
     * @return $this
     * @throws RuntimeException
     */
    public function execute()
    {
        if ($this->header->state !== ContextState::PENDING || !count($this->middlewares)) {
            return $this;
        }

        $this->header->state = ContextState::RUNNING;
        $this->header->cursor = 0;
        $this->header->startTime = microtime(true);
        $this->header->endTime = null;
        $this->header->elapsedTime = null;

        for (; $this->header->cursor < $this->header->total; $this->header->cursor++) {
            $middleware = $this->middlewares[$this->header->cursor];

            $this->current->route = $middleware['current']['route'];
            $this->current->httpMethod = $middleware['current']['httpMethod'];
            $this->current->uri = $middleware['current']['uri'];
            $this->current->friendly = $middleware['current']['friendly'];
            $this->current->params = (object)$middleware['current']['params'];

            $this->result = $this->call(
                $middleware['callable'],
                $middleware['params'],
                $middleware['construct']
            );

            if ($this->header->state !== ContextState::RUNNING) {
                break;
            }
        }

        if ($this->header->state === ContextState::RUNNING) {
            $this->header->state = ContextState::COMPLETED;
        }
        $this->header->endTime = microtime(true);
        $this->header->elapsedTime = $this->header->endTime - $this->header->startTime;

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
     * @return $this
     */
    public function reset()
    {
        $this->header->cursor = -1;
        $this->header->state = ContextState::PENDING;
        $this->header->startTime = null;
        $this->header->endTime = null;
        $this->header->elapsedTime = null;
        $this->current->route = null;
        $this->current->httpMethod = null;
        $this->current->uri = null;
        $this->current->friendly = null;
        $this->current->params = null;
        $this->data = null;
        $this->result = null;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
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
