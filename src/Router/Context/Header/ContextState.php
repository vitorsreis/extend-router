<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @phpcs:disable
 */

namespace VSR\Extend\Router\Context\Header;

class ContextState
{
    const PENDING = 0;
    const RUNNING = 2;
    const COMPLETED = 3;
    const STOPPED = 4;
}
