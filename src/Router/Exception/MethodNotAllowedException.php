<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Exception;

class MethodNotAllowedException extends RuntimeException
{
    /**
     * @var string[]
     */
    public $allowedMethods = [];

    public function __construct($message, $httpCode, $allowedMethods, $previous = null)
    {
        parent::__construct($message, $httpCode, $previous);
        $this->allowedMethods = $allowedMethods;
    }
}
