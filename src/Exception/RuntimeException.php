<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Exception;

use Exception;

class RuntimeException extends Exception
{
    /**
     * @param string $message
     * @param int $httpCode
     * @param $previous
     */
    public function __construct($message, $httpCode, $previous = null)
    {
        parent::__construct($message, $httpCode, $previous);
    }
}
