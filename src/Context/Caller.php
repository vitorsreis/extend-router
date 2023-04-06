<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Context;

use D5WHUB\Extend\Router\Exception\RuntimeException;

trait Caller
{
    /**
     * @throws RuntimeException
     */
    private function call(array|callable|string $callable, array $params, array|null $construct): mixed
    {
        if (!is_null($construct)) {
            $callable[0] = new $callable[0](...$this->populate($construct));
        }

        return call_user_func_array($callable, $this->populate($params));
    }

    /**
     * @throws RuntimeException
     */
    private function populate(array $params): array
    {
        $result = [];

        foreach ($params as $name => $argument) {
            if ($argument['type'] === 'context') {
                $result[$name] = $this;
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
