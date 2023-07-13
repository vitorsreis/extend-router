<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace D5WHUB\Extend\Router\Cache;

use Exception;
use RuntimeException;

class Memcached extends AbstractCache implements CacheInterface
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
    public function __construct($host, $port, $weight = 0)
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
        ($value = $this->serialize($value)) && $this->memcached->set($key, $value);
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
