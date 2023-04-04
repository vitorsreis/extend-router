<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Cache;

interface Cache
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value): void;
}
