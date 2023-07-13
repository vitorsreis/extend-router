<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Cache;

interface CacheInterface
{
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return void
     */
    public function del($key);

    /**
     * @return void
     */
    public function clear();
}
