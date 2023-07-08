<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Test\Extend\Router\UnitTest;

use D5WHUB\Extend\Router\Context;

class MiddlewareByClassStaticMethod
{
    /**
     * @param $var1
     * @param $var2
     * @return int
     */
    public static function params($var1, $var2)
    {
        return $var1 + $var2;
    }

    /**
     * @param Context $context
     * @return int
     */
    public static function context(Context $context)
    {
        return $context->current->params->var1 + $context->current->params->var2;
    }
}
