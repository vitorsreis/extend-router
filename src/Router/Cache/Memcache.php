<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace VSR\Extend\Router\Cache;

use RuntimeException;

class Memcache extends AbstractCache
{
    /**
     * @var \Memcache
     */
    private $memcache;

    /**
     * @param string $host
     * @param int $port
     * @param int $timeout
     */
    public function __construct($host = '127.0.0.1', $port = 11211, $timeout = 10)
    {
        if (!extension_loaded('memcache')) {
            throw new RuntimeException('Extension Memcache not loaded', 500);
        }

        $this->memcache = new \Memcache();
        if (!$this->memcache->connect($host, $port, $timeout)) {
            throw new RuntimeException('Memcache connection failed', 500);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!$this->allowed($key)) {
            return $default;
        }

        $value = $this->memcache->get($key);
        if ($value === false) {
            return $default;
        }

        $value = $this->unserialize($value);
        return $value !== null ? $value : $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->allowed($key) && $this->memcache->get($key) !== false;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        if (!$this->allowed($key)) {
            return;
        }

        ($value = $this->serialize($value)) !== null && $this->memcache->set($key, $value);
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        $this->memcache->delete($key);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->memcache->flush();
    }
}
