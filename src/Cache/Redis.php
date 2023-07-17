<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace D5WHUB\Extend\Router\Cache;

use Exception;
use RuntimeException;

class Redis extends AbstractCache
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @param string|null $password
     * @param int|null $database
     */
    public function __construct($host = '127.0.0.1', $port = 6379, $timeout = 10, $password = null, $database = null)
    {
        if (!extension_loaded('redis')) {
            throw new RuntimeException('Extension Redis not loaded', 500);
        }

        $this->redis = new \Redis();
        try {
            $this->redis->connect($host, $port, $timeout);

            if ($password !== null) {
                $this->redis->auth($password);
            }

            if ($database !== null) {
                $this->redis->select($database);
            }
        } catch (Exception $e) {
            throw new RuntimeException('Redis connection failed', 500, $e);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        try {
            $value = $this->redis->get($key);
        } catch (Exception $e) {
            return $default;
        }

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
        try {
            return $this->redis->exists($key);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        try {
            ($value = $this->serialize($value)) !== null && $this->redis->set($key, $value);
        } catch (Exception $e) {
        }
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        try {
            $this->redis->del($key);
        } catch (Exception $e) {
        }
    }

    /**
     * @return void
     */
    public function clear()
    {
        try {
            $this->redis->flushDB();
        } catch (Exception $e) {
        }
    }
}
