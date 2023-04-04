<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Router\Context;

class Current
{
    public int|null $route;

    public string|null $httpMethod;

    public string|null $uri;

    public string|null $friendly;

    public object|null $params;
}
