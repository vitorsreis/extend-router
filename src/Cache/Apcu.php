<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace D5WHUB\Extend\Router\Cache;

use RuntimeException;

class Apcu extends AbstractCache implements CacheInterface
{
    public function __construct()
    {
        if (!extension_loaded('apcu')) {
            throw new RuntimeException('Extension APCu not loaded', 500);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = apcu_fetch($key, $success);
        if ($success === false) {
            $value = $default;
        } else {
            $value = $this->unserialize($value);
        }
        return $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return apcu_exists($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        ($value = $this->serialize($value)) && apcu_store($key, $value);
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        apcu_delete($key);
    }

    /**
     * @return void
     */
    public function clear()
    {
        apcu_clear_cache();
    }
}
