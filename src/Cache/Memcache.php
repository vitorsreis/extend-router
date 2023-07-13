<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace D5WHUB\Extend\Router\Cache;

use Exception;
use RuntimeException;

class Memcache extends AbstractCache implements CacheInterface
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
    public function __construct($host, $port, $timeout = 10)
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
        $value = $this->memcache->get($key);
        if ($value === false) {
            $value = $default;
        }
        return $this->unserialize($value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->memcache->get($key) !== false;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        ($value = $this->serialize($value)) && $this->memcache->set($key, $value);
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
