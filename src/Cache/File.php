<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Cache;

use RuntimeException;

class File extends AbstractCache implements CacheInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        if (!is_dir($path) || !mkdir($path, 0644, true)) {
            throw new RuntimeException('Path not found', 500);
        }

        if (!is_writable($path)) {
            throw new RuntimeException('Path not writable', 500);
        }

        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $key
     * @return string
     */
    private function getFileName($key)
    {
        return $this->path . DIRECTORY_SEPARATOR . trim($key, DIRECTORY_SEPARATOR) . '.cache';
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $filename = $this->getFileName($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $value = file_get_contents($filename);
        if ($value === false) {
            return $default;
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
        return file_exists($this->getFileName($key));
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        ($value = $this->serialize($value)) && file_put_contents($this->getFileName($key), serialize($value));
    }

    /**
     * @param string $key
     * @return void
     */
    public function del($key)
    {
        unlink($this->getFileName($key));
    }

    /**
     * @return void
     */
    public function clear()
    {
        foreach (glob($this->path . DIRECTORY_SEPARATOR . '*.cache') as $file) {
            unlink($file);
        }
    }
}
