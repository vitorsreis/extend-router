<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Test\Extend\Router\UnitTest;

class MiddlewareByClassMethodWithParams
{
    public function execute($var1, $var2): int
    {
        return $var1 + $var2;
    }
}
