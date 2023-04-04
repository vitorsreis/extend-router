<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Context\Header;

enum ContextState: int
{
    case PENDING = 0;
    case RUNNING = 2;
    case COMPLETED = 3;
    case STOPPED = 4;
}
