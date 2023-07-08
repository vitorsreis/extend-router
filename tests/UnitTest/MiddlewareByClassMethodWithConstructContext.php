<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Test\Extend\Router\UnitTest;

use D5WHUB\Extend\Router\Context;

class MiddlewareByClassMethodWithConstructContext
{
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return int
     */
    public function execute()
    {
        return $this->context->current->params->var1 + $this->context->current->params->var2;
    }
}
