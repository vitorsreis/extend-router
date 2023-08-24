<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Context;

class Current
{
    /**
     * @var int|null
     */
    public $route;

    /**
     * @var string|null
     */
    public $httpMethod;

    /**
     * @var string|null
     */
    public $uri;

    /**
     * @var string|null
     */
    public $friendly;

    /**
     * @var object|null
     */
    public $params;
}
