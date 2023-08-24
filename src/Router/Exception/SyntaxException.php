<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace VSR\Extend\Router\Exception;

use Exception;

class SyntaxException extends Exception
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
