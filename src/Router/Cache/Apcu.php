<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace VSR\Extend\Router\Cache;

use RuntimeException;

class Apcu extends AbstractCache
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
        if (!$this->allowed($key)) {
            return $default;
        }

        $value = apcu_fetch($key, $success);
        if ($success === false) {
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
        return $this->allowed($key) && apcu_exists($key);
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

        ($value = $this->serialize($value)) !== null && apcu_store($key, $value);
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
