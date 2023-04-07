<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

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

    public readonly Current $current;

    public readonly Header $header;

    /**
     * @var mixed Partial result
     */
    public mixed $result = null;

    /**
     * @var mixed Persist execution data
     */
    private mixed $data = null;

    private readonly array $middlewares;

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
     * @throws RuntimeException
     */
    public function execute(): self
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

    public function stop(): self
    {
        $this->header->state = ContextState::STOPPED;
        return $this;
    }

    public function reset(): self
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

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
}
