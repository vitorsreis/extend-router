<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Test\Extend\Router\UnitTest;

use D5WHUB\Extend\Router\Context;

class MiddlewareByClassMethodWithConstructContext
{
    public function __construct(
        protected Context $context
    ) {
    }

    public function execute(): int
    {
        return $this->context->current->params->var1 + $this->context->current->params->var2;
    }
}
