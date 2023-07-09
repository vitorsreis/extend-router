<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Cache;

class Memory implements CacheInterface
{
    /**
     * @var array<string, mixed>
     */
    private $memory = [];

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->memory[$key]) ? $this->memory[$key] : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->memory);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->memory[$key] = $value;
    }
}
