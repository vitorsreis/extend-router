<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @phpcs:disable
 */

namespace VSR\Extend\Router\Cache;

abstract class AbstractCacheAllow implements CacheInterface
{
    const FLAG_ROUTER = 1;
    const FLAG_MATCH = 2;
    const FLAG_EXECUTE = 4;
    const FLAG_OTHERS = 8;
    const FLAG_ALL = self::FLAG_ROUTER | self::FLAG_MATCH | self::FLAG_EXECUTE | self::FLAG_OTHERS;

    protected $allowed_flags = self::FLAG_ALL;

    /**
     * @param string $key
     * @return int
     */
    protected function allowed($key)
    {
        if (preg_match('/^(router|match|execute)-/', $key, $matches)) { # check by key prefix
            switch ($matches[1]) {
                case 'router':
                    return $this->allowed_flags & self::FLAG_ROUTER;
                case 'match':
                    return $this->allowed_flags & self::FLAG_MATCH;
                case 'execute':
                    return $this->allowed_flags & self::FLAG_EXECUTE;
            }
        }
        return $this->allowed_flags & self::FLAG_OTHERS;
    }

    /**
     * @param int $flags FLAG_ROUTER | FLAG_MATCH | FLAG_EXECUTE | FLAG_OTHERS | FLAG_ALL
     * @return void
     */
    public function allowCache($flags)
    {
        $this->allowed_flags |= $flags;
    }

    /**
     * @param int $flags FLAG_ROUTER | FLAG_MATCH | FLAG_EXECUTE | FLAG_OTHERS | FLAG_ALL
     */
    public function disallowCache($flags)
    {
        $this->allowed_flags &= ~$flags;
    }
}
