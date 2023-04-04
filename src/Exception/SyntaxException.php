<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Exception;

use Exception;
use Throwable;

class SyntaxException extends Exception
{
    public function __construct(string $message, int $httpCode, Throwable|null $previous = null)
    {
        parent::__construct($message, $httpCode, $previous);
    }
}
