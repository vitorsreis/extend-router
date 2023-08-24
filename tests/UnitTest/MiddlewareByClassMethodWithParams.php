<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Test\Extend\Router\UnitTest;

class MiddlewareByClassMethodWithParams
{
    public function execute($var1, $var2)
    {
        return $var1 + $var2;
    }
}
