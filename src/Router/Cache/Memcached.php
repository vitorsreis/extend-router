<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace VSR\Extend\Router\Cache;

use RuntimeException;

class Memcached extends AbstractCache
{
    /**
     * @var \Memcached
     */
    private $memcached;

    /**
     * @param string $host
     * @param int $port
     * @param int $weight
     */
    public function __construct($host = '127.0.0.1', $port = 11211, $weight = 0)
    {
        if (!extension_loaded('memcached')) {
            throw new RuntimeException('Extension Memcached not loaded', 500);
        }

        $this->memcached = new \Memcached();
        if (!$this->memcached->addServer($host, $port, $weight)) {
            throw new RuntimeException('Memcached connection failed', 500);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->memcached->get($key);
        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
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
        $this->memcached->get($key);
        return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        ($value = $this->serialize($value)) !== null && $this->memcached->set($key, $value);
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        $this->memcached->delete($key);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->memcached->flush();
    }
}
