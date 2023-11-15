<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
 */

namespace VSR\Extend\Router\Context;

use Exception;
use Throwable;
use VSR\Extend\Caller\Resolver as CallerResolver;
use VSR\Extend\Router\Exception\RuntimeException;

trait Resolver
{
    use CallerResolver;

    /**
     * @param array|callable|string $callable
     * @param array $parameters
     * @param array $construct
     * @return mixed
     * @throws RuntimeException
     */
    private function resolveWithContext($callable, $parameters = [], $construct = [])
    {
        try {
            /** @noinspection PhpParamsInspection */
            return static::resolve(
                $callable,
                ['context' => $this] + $parameters,
                ['context' => $this] + $construct,
                $this
            );
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        }
    }
}
