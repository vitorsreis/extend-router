<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Test\Extend\Router\UnitTest;

use D5WHUB\Extend\Router\Context;

class MiddlewareByClassStaticMethod
{
    public static function params($var1, $var2): int
    {
        return $var1 + $var2;
    }

    public static function context(Context $context): int
    {
        return $context->current->params->var1 + $context->current->params->var2;
    }
}
