<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Cache;

class Memory extends AbstractCacheAllow implements CacheInterface
{
    /**
     * @var array<string, mixed>
     */
    private $memory = [];

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $this->disallowCache(self::FLAG_ROUTER); // ignore router cache in memory

        if (!$this->allowed($key)) {
            return $default;
        }

        return $this->has($key) ? $this->memory[$key] : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $this->disallowCache(self::FLAG_ROUTER); // ignore router cache in memory

        return $this->allowed($key) && array_key_exists($key, $this->memory);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->disallowCache(self::FLAG_ROUTER); // ignore router cache in memory

        if (!$this->allowed($key)) {
            return;
        }

        $this->memory[$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        unset($this->memory[$key]);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->memory = [];
    }
}
