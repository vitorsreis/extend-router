<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Context;

use D5WHUB\Extend\Router\Context\Header\ContextState;

class Header
{
    public function __construct(
        public int          $cursor = -1,
        public int          $total = -1,
        public ContextState $state = ContextState::PENDING,
        public float|null   $startTime = null,
        public float|null   $endTime = null,
        public float|null   $elapsedTime = null
    ) {
    }
}
