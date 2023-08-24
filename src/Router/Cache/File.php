<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Cache;

use RuntimeException;

class File extends AbstractCache
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @param string $dir
     */
    public function __construct($dir)
    {
        if (!is_dir($dir) && !mkdir($dir, 0644, true)) {
            throw new RuntimeException('Path not found', 500);
        }

        if (!is_writable($dir)) {
            throw new RuntimeException('Path not writable', 500);
        }

        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $key
     * @return string
     */
    private function getFileName($key)
    {
        return $this->dir . DIRECTORY_SEPARATOR . trim($key, DIRECTORY_SEPARATOR) . '.cache';
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

        $filename = $this->getFileName($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $value = file_get_contents($filename);
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
        return $this->allowed($key) && file_exists($this->getFileName($key));
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

        ($value = $this->serialize($value)) !== null && file_put_contents($this->getFileName($key), $value);
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
        foreach (glob($this->dir . DIRECTORY_SEPARATOR . '*.cache') as $file) {
            unlink($file);
        }
    }
}
