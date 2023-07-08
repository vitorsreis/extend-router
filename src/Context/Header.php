<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Context;

use D5WHUB\Extend\Router\Context\Header\ContextState;

class Header
{
    /**
     * @var int
     */
    public $cursor = -1;

    /**
     * @var int
     */
    public $total = -1;

    /**
     * @var int
     */
    public $state = ContextState::PENDING;

    /**
     * @var float|null
     */
    public $startTime = null;

    /**
     * @var float|null
     */
    public $endTime = null;

    /**
     * @var float|null
     */
    public $elapsedTime = null;

    public function __construct(
        $cursor = -1,
        $total = -1,
        $state = ContextState::PENDING,
        $startTime = null,
        $endTime = null,
        $elapsedTime = null
    ) {
        $this->elapsedTime = $elapsedTime;
        $this->endTime = $endTime;
        $this->startTime = $startTime;
        $this->state = $state;
        $this->total = $total;
        $this->cursor = $cursor;
    }
}
