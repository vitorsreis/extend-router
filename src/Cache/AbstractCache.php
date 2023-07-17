<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Cache;

use D5WHUB\Extend\Router\Exception\RuntimeException;
use D5WHUB\Extend\Router\Router;
use Exception;
use ReflectionException;
use ReflectionFunction;

abstract class AbstractCache implements CacheInterface
{
    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    abstract public function get($key, $default = null);

    /**
     * @param string $key
     * @return bool
     */
    abstract public function has($key);

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    abstract public function set($key, $value);

    /**
     * @param string $key
     * @return void
     */
    abstract public function del($key);

    /**
     * @return void
     */
    abstract public function clear();

    /**
     * Serialize value to string
     * @param mixed $value
     * @return string|null
     */
    protected function serialize($value)
    {
        try {
            return serialize($value);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        try {
            return unserialize($value);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create router cached or callback and cache it
     * @param callable $callback function(Router $router)
     * @param string|null $hash If null, will auto generate hash based on callback source code
     * @param array<string, string>|false $warning false if success, or array of failure messages:<table>
     * <tr><th>Key</th><th>Value</th></tr>
     * <tr><td>NOT_FOUND</td><td>Cache not found or hash mismatch</td></tr>
     * <tr><td>HASH_FAILED</td><td>Invalid generate hash, because ...</td></tr>
     * <tr><td>LOAD_FAILED</td><td>Failed to load router map from cache, because ...</td></tr>
     * <tr><td>SAVE_FAILED</td><td>Unable to cache router map, because ...</td></tr>
     * </table>
     * @return Router
     * @throws RuntimeException
     */
    public function createRouter($callback, &$hash = null, &$warning = false)
    {
        $warning = [];
        try {
            if ($hash === null) {
                # hash sha1 based on callback source code
                try {
                    $rf = new ReflectionFunction($callback);
                    $code = file($rf->getFileName());
                    $code = array_slice($code, $rf->getStartLine() - 1, $rf->getEndLine() - $rf->getStartLine() + 1);
                    $code = implode('', $code);
                    $hash = sha1($code);
                    unset($rf, $code);
                } catch (ReflectionException $e) {
                    $hash = null;
                    $warning["HASH_FAILED"] = 'Invalid generate hash, because ' . $e->getMessage();
                }
            }
            if ($hash !== null) {
                if ($this->has("router-map") && $hash === $this->get("router-hash")) {
                    try {
                        /** @var Router $router */
                        $router = unserialize($this->get("router-map"));
                        $router->setCache($this);
                        return $router;
                    } catch (Exception $e) {
                        $warning["LOAD_FAILED"] = 'Failed to load router map from cache, because ' . $e->getMessage();
                    }
                } else {
                    $warning["NOT_FOUND"] = 'Cache not found or hash mismatch';
                }
            }

            $this->clear(); // clear cache on rebuild
            $router = new Router($this);
            $callback($router);

            try {
                if ($hash !== null) {
                    $this->set("router-map", serialize($router));
                    $this->set("router-hash", $hash);
                }
            } catch (Exception $e) {
                if (preg_match('/^Serialization of (.*) is not allowed$/', $e->getMessage(), $matches)) {
                    $warning["SAVE_FAILED"] = "Unable to cache router map, because $matches[1] is not allowed";
                } else {
                    $warning["SAVE_FAILED"] = 'Unable to cache router map, because ' . $e->getMessage();
                }
            }

            return $router;
        } finally {
            $warning = $warning ?: false;
        }
    }
}
