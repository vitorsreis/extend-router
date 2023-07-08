<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Context;

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
