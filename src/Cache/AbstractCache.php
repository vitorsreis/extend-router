<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Cache;

use Exception;

abstract class AbstractCache
{
    /**
     * Serialize value to string
     * @param mixed $value
     * @return string
     */
    protected function serialize($value)
    {
        try {
            return serialize($value);
        } catch (Exception $e) {
            return null;
        }
    }


    /**
     * @param string $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        try {
            return unserialize($value);
        } catch (Exception $e) {
            return null;
        }
    }
}
