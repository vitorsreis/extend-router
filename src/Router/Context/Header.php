<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Context;

use VSR\Extend\Router\Context\Header\ContextState;

class Header
{
    /**
     * @var string Execution hash
     */
    public $hash = null;

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
