<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Cache;

class Memory implements Cache
{
    private array $memory = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->memory[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->memory);
    }

    public function set(string $key, mixed $value): void
    {
        $this->memory[$key] = $value;
    }
}
