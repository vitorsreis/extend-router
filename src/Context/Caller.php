<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Context;

use D5WHUB\Extend\Router\Exception\RuntimeException;
use ReflectionClass;
use ReflectionException;

trait Caller
{
    /**
     * @param array|callable|string $callable
     * @param array $params
     * @param array|null $construct
     * @return mixed
     * @throws RuntimeException
     */
    private function call(&$callable, $params, $construct)
    {
        if (!is_null($construct) && is_string($callable[0])) {
            try {
                $callable[0] = (new ReflectionClass($callable[0]))
                    ->newInstanceArgs($this->populate($construct));
            } catch (ReflectionException $e) {
                throw new RuntimeException($e->getMessage(), 500);
            }
        }

        return call_user_func_array($callable, $this->populate($params));
    }

    /**
     * @param array $params
     * @return array
     * @throws RuntimeException
     */
    private function populate($params)
    {
        $result = [];

        foreach ($params as $name => $argument) {
            if ($argument['type'] === 'context') {
                $result[$name] = $this;
            } elseif (isset($argument['value'])) {
                $result[$name] = $argument['value'];
            } elseif (isset($this->current->params->$name)) {
                $result[$name] = $this->current->params->$name;
            } elseif (array_key_exists('default', $argument)) {
                $result[$name] = $argument['default'];
            } else {
                throw new RuntimeException(
                    sprintf("Required argument \"%s\" for invoke \"%s\"!", $name, $argument['name']),
                    500
                );
            }
        }

        return $result;
    }
}
