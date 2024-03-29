# Very elegant, fast and powerful router for PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/vitorsreis/extend-router?style=flat-square&label=stable&color=2E9DD3)](https://packagist.org/packages/vitorsreis/extend-router)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/vitorsreis/extend-router/php?style=flat-square&color=777BB3)](https://packagist.org/packages/vitorsreis/extend-router)
[![License](https://img.shields.io/packagist/l/vitorsreis/extend-router?style=flat-square&color=418677)](https://github.com/vitorsreis/extend-router/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/vitorsreis/extend-router?style=flat-square&color=0476B7)](https://packagist.org/packages/vitorsreis/extend-router)
[![Repo Stars](https://img.shields.io/github/stars/vitorsreis/extend-router?style=social)](https://github.com/vitorsreis/extend-router)

Indexed by words tree and regex matching, this router is very elegant, fast, and powerful. Architected as a queue of
merged middlewares (not unique match), it offers multiple interactions in routes with cache, contexts, and persistent
data.
Unit tests passed on versions: ```5.6```, ```7.4```, ```8.1``` and ```8.2```

---

## Benchmark

Check out benchmark with leading public libraries [here](/tests/Benchmark/Benchmark.md).

---

## Install

```shell
composer require vitorsreis/extend-router
```

## Usage

```php
$router = new \VSR\Extend\Router();
$router->get('/product/:id', function ($id) { echo "product $id"; });
$router->match('GET', '/product/100')->execute();
// output: "product 100"
```

---

### Router group

You can add group of routes:

```php
$router->group('/product', function (\VSR\Extend\Router $router) {
    $router->get('/:id', function ($id) { echo "get product $id"; });
    $router->post('/:id', function ($id) { echo "post product $id"; });
});
$router->match('POST', '/product/100')->execute();
// output: "post product 100"
```

---

### 404 Not Found and 405 Method Not Allowed

- You can use NotFoundException and MethodNotAllowedException in catch:

```php
try {
    $router->match('POST', '/aaa');
} catch (\VSR\Extend\Router\Exception\NotFoundException $e) {
    echo "{$e->getCode()}: {$e->getMessage()}"; // output: "404: Route \"/aaa\" not found"
} catch (\VSR\Extend\Router\Exception\MethodNotAllowedException $e) {
    echo "{$e->getCode()}: {$e->getMessage()}"; // output: "405: Method \"POST\" not allowed for route \"/aaa\""
}
```

- Or can use RuntimeException in catch:

```php
try {
    $router->match('POST', '/aaa');
} catch (\VSR\Extend\Router\Exception\RuntimeException $e) {
    if (in_array($e->getCode(), [404, 405])) {
        echo "{$e->getCode()}: {$e->getMessage()}";
        // output: "404: Route \"/aaa\" not found" or  "405: Method \"POST\" not allowed for route \"/aaa\""
    } else {
        throw $e;
    }
}
```

- In case 405, you can get allowed methods:

```php
try {
    $router->match('PUT', '/aaa');
} catch (\VSR\Extend\Router\Exception\MethodNotAllowedException $e) {
    echo implode(', ', $e->allowedMethods); // output: "GET, POST, ..."
}
```

---

### Friendly URIs

You can add friendly URIs to redirect to specific routes:

```php
$router->post('/product/:id', function ($id) { echo "post product $id"; });
$router->friendly('/iphone', '/product/100');
$router->match('POST', '/iphone')->execute();
// output: "post product 100"
```

---

### Filters

Filters are used to add regex to route variables in a nicer and cleaner way.

```php
$router->get('/:var1[09]', function ($var1) { return "[09] : $var1"; });
$router->get('/:var1[az]', function ($var1) { return "[az] : $var1"; });

echo $router->match('GET', '/111')->execute()->result; // output: "[09] : 111"
echo $router->match('GET', '/aaa')->execute()->result; // output: "[az] : aaa"
```

You can use loose filter in routes:

```php
$router->get('/[09]', function () { return "[09]"; });
$router->get('/[az]', function () { return "[az]"; });

echo $router->match('GET', '/111')->execute()->result; // output: "[09]"
echo $router->match('GET', '/aaa')->execute()->result; // output: "[az]"
```

You can add custom filters:

```php
$router->addFilter('custom_only_numeric', '\d+')
$router->get('/:var1[custom_only_numeric]', function ($var1) { return "CUSTOM_VAR1_FILTER : $var1"; });

$router->addFilter('custom_w10', '\w{10}')
$router->get('/[custom_w10]', function () { return 'CUSTOM_LOOSE_FILTER'; });
```

Below are pre-registered filters:

|            Key             |         Description         | Regex                                                                           |
|:--------------------------:|:---------------------------:|---------------------------------------------------------------------------------|
| _&lt;omitted or empty&gt;_ |     any other than "/"      | \[^\/]+                                                                         |
|             09             |        only numbers         | \[0-9]+                                                                         |
|             az             |       only lowercase        | \[a-z]+                                                                         |
|             AZ             |       only uppercase        | \[A-Z]+                                                                         |
|             aZ             |        only letters         | \[a-zA-Z]+                                                                      |
|             d              |        only numbers         | \d+                                                                             |
|             D              |    any other than "0-9"     | \D+                                                                             | 
|             w              |      only "a-zA-Z0-9_"      | \w+                                                                             |
|             W              | any other than "a-zA-Z0-9_" | \W+                                                                             |
|            uuid            |            uuid             | \[0-9a-f]{8}-\[0-9a-f]{4}-\[0-5]\[0-9a-f]{3}-\[089ab]\[0-9a-f]{3}-\[0-9a-f]{12} |

---

### Allowing/Disallowing methods
Default list: ```GET```, ```POST```, ```PUT```, ```PATCH```, ```DELETE```, ```OPTIONS```, ```HEAD```

```php
$router->allowMethod('XXX', ...);
// $router->addRoute('XXX', '/aaa', function () { ... }); = OK
// $router->match('XXX', '/aaa'); = OK

$router->disallowMethod('GET', ...);
// $router->addRoute('GET', '/aaa', function () { ... }); = throw SyntaxException(500)
// $router->match('GET', '/aaa'); = throw RuntimeException(400)
```

---

### Cache

- This usage mode or ```Memory``` cache does not store the router map.

```php
$cache = new \VSR\Extend\Router\Cache\Memory();
// $cache = new \VSR\Extend\Router\Cache\File(__DIR__ . "/cache/");
// $cache = new \VSR\Extend\Router\Cache\Apcu();
// $cache = new \VSR\Extend\Router\Cache\Memcache();
// $cache = new \VSR\Extend\Router\Cache\Memcached();
// $cache = new \VSR\Extend\Router\Cache\Redis();

$router = new \VSR\Extend\Router($cache);
$router->get('/product/:id', function ($id) { echo "product $id"; });
$router->friendly('/iphone-xs', '/product/100');
$router->friendly('/samsumg-s10', '/product/200');
$router->match('GET', '/iphone-xs')->execute();
// output: "product 100"
```

- Use like this to instantiate already stored router or, if it doesn't exist, instantiate it and then store it in cache.
  This greatly reduces time from second load onwards. **Does not support routes with anonymous classes or
  anonymous/arrow functions (Closure objects)**
    - ```&$hash``` argument can be used to control the cache version, if omitted, it is based on the ```$callback```
      source code by reflection.
    - ```&$warning``` argument if different from ```false```", indicates that an error occurred.

```php
$cache = new \VSR\Extend\Router\Cache\File(__DIR__ . "/cache/");
// $cache = new \VSR\Extend\Router\Cache\Apcu();
// $cache = new \VSR\Extend\Router\Cache\Memcache();
// $cache = new \VSR\Extend\Router\Cache\Memcached();
// $cache = new \VSR\Extend\Router\Cache\Redis();

function callback($id) {
    echo "product $id";
}

$router = $cache->createRouter(function (\VSR\Extend\Router $router) {
    $router->get('/product/:id', 'callback');
    $router->friendly('/iphone-xs', '/product/100');
    $router->friendly('/samsumg-s10', '/product/200');
}, $hash, $warning);
$router->match('GET', '/iphone-xs')->execute();
// output: "product 100"
```

- Allow\Disallow cache prefixes for store.
    - ```FLAG_ROUTER``` to enable/disable cache router
    - ```FLAG_MATCH``` to enable/disable cache match
    - ```FLAG_EXECUTE``` to enable/disable cache execute
    - ```FLAG_OTHERS``` to enable/disable cache others
    - ```FLAG_ALL``` to enable/disable cache all

```php
$cache = new \VSR\Extend\Router\Cache\Memory();
$cache->disallowCache($cache::FLAG_ALL);
$cache->allowCache($cache::FLAG_ROUTER);
```

---

### Context param

Context contains all information about the current execution. Use an argument with the name "$context" of type "
omitted,""mixed," or "\VSR\Extend\Router\Context" in middlewares or in the constructor of a class if the middleware is
of type non-static class method

```php
$router->get('/aaa', function ($context) { ... });
$router->any('/aaa', function (mixed $context) { ... }); # Explicit mixed type only PHP 8+
$router->get('/a*', function (\VSR\Extend\Router\Context $context) { ... });
$router->any('/a*', function (\VSR\Extend\Router\Context $custom_name_context) { ... });
```

---

### Context methods/properties

| Property                                   | Description                                                                        |
|:-------------------------------------------|:-----------------------------------------------------------------------------------|
| ```$context->current->route```             | Current match middleware route                                                     |
| ```$context->current->method```            | Current match middleware method                                                    |
| ```$context->current->uri```               | Current match middleware uri                                                       |
| ```$context->current->friendly```          | Current match middleware friendly uri                                              |
| ```$context->current->params```            | Current match middleware uri variables                                             |
| ```$context->header->hash```               | Current execution hash                                                             |
| ```$context->header->cursor```             | Current execution position on queue middleware                                     |
| ```$context->header->total```              | Total execution queue middlewares count                                            |
| ```$context->header->state```              | Current execution state                                                            |
| ```$context->header->startTime```          | Execution start time                                                               |
| ```$context->header->endTime```            | Execution end time                                                                 |
| ```$context->header->elapsedTime```        | Execution time                                                                     |
| ```$context->cached```                     | Execution result is cached                                                         |
| ```$context->allowedMethods```             | Current match allowed methods                                                      |
| ```$context->result```                     | Partial/Final execution result                                                     |
| ```$context->execute(?$callback)```        | Start execution, ```$callback``` is optional and available argument ```$context``` |
| ```$context->stop()```                     | Stop execution                                                                     |
| ```$context->get($key, $default = null)``` | Get persistent data                                                                |
| ```$context->set($key, $value)```          | Set persistent data                                                                |
| ```$context->has($key)```                  | Check if persistent data exists                                                    |

---

### Persisting data

You can persist data in context so that it is persisted in future callbacks.

```php
$router->get('/aaa', function (\VSR\Extend\Router\Context $context) {
    $context->set('xxx', $context->get('xxx', 0) + 10); # 2. Increment value: 5 + 10 = 15
});
$router->get('/var2', function (\VSR\Extend\Router\Context $context) {
    $context->set('xxx', $context->get('xxx', 0) + 15); # 3. Increment value: 15 + 15 = 30
});
$context = $router->match('GET', '/aaa')
    ->set('xxx', 5) # 1. Initial value: 5
    ->execute();

echo $context->get('xxx');
// output: "30"
```

---

### Execute with callback

You can run receiving callbacks every middleware run with current context.

```php
$router->post('/:aa', function ($aa) { return "a1:$aa "; }, function ($aa) { return "a2:$aa "; });
$router->post('/:bb', function ($bb) { return "bb:$bb "; });
$router->match('POST', '/99')->execute(function ($context) {
    // partial result, run 3 times
    echo '[' .
        "{$context->header->cursor}/{$context->header->total} " .
        "{$context->current->method} {$context->current->route} = $context->result" .
    '], ';
});
// output: "[1/3 POST /:aa = a1:99], [2/3 POST /:aa = a2:99], [3/3 POST /:bb = bb:99], "
```

---

### Middleware with arguments

Route variables and context param are not mandatory in callbacks, so they can be omitted without problems.

```php
$router->any('/:var1/:var2', function () { ... });
$router->any('/:var1/:var2', function ($var1) { ... });
$router->any('/:var1/:var2', function ($var2) { ... });
$router->any('/:var1/:var2', function ($context) { ... });
$router->any('/:var1/:var2', function (mixed $context) { ... }); # Explicit mixed type only PHP 8+
$router->any('/:var1/:var2', function (\VSR\Extend\Router\Context $context) { ... });
$router->any('/:var1/:var2', function (\VSR\Extend\Router\Context $custom_name_context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2) { ... });
$router->any('/:var1/:var2', function ($var1, $context) { ... });
$router->any('/:var1/:var2', function ($var1, mixed $context) { ... }); # Explicit mixed type only PHP 8+
$router->any('/:var1/:var2', function ($var1, \VSR\Extend\Router\Context $context) { ... });
$router->any('/:var1/:var2', function ($var1, \VSR\Extend\Router\Context $custom_name_context) { ... });
$router->any('/:var1/:var2', function ($var2, $context) { ... });
$router->any('/:var1/:var2', function ($var2, mixed $custom_name_context) { ... }); # Explicit mixed type only PHP 8+
$router->any('/:var1/:var2', function ($var2, \VSR\Extend\Router\Context $context) { ... });
$router->any('/:var1/:var2', function ($var2, \VSR\Extend\Router\Context $custom_name_context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2, $context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2, mixed $context) { ... }); # Explicit mixed type only PHP 8+
$router->any('/:var1/:var2', function ($var1, $var2, \VSR\Extend\Router\Context $context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2, \VSR\Extend\Router\Context $custom_name_context) { ... });
```

---

### Middleware queue

With the "not unique match" pattern, you can have multiple callbacks in queue per order of addition for an uri.

```php
$router->get('/aaa', function () { echo "11 "; }, function () { echo "12 "; }, function () { echo "13 "; });
$router->any('/aaa', function () { echo "2 "; });
$router->get('/a*', function () { echo "3 "; });
$router->any('/a*', function () { echo "4 "; });
$router->get('*', function () { echo "5 "; });
$router->any('*', function () { echo "6 "; });
$router->get('/:var', function ($var) { echo "7 "; });
$router->any('/:var', function ($var) { echo "8 "; });
$router->match('GET', '/aaa')->execute();
// output: "11 12 13 2 3 4 5 6 7 8 "
```

You can stop the queue using "stop" method of context

```php
$router->get('/aaa', function () { echo "11 "; }, function () { echo "12 "; }, function () { echo "13 "; });
$router->any('/aaa', function () { echo "2 "; });
$router->get('/a*', function () { echo "3 "; });
$router->any('/a*', function (\VSR\Extend\Router\Context $context) { echo "4 "; $context->stop(); });
$router->get('*', function () { echo "5 "; });
$router->any('*', function () { echo "6 "; });
$router->get('/:var', function ($var) { echo "7 "; });
$router->any('/:var', function ($var) { echo "8 "; });
$router->match('GET', '/aaa')->execute();
// output: "11 12 13 2 3 4 "
```

---

### Supported middleware types

```php
// by native function name
$router->any('/:haystack/:needle', "stripos");

#--------------------------------------------------

// by function name
function callback($var1, $var2, $context) { ... }
$router->any('/:var1/:var2', "callback");

#--------------------------------------------------

// by anonymous function
$router->any('/:var1/:var2', function ($var1, $var2, $context) { ... });
$router->any('/:var1/:var2', static function ($var1, $var2, $context) { ... });

#--------------------------------------------------

// by arrow function, PHP 7.4+
$router->any('/:var1/:var2', fn($var1, $var2, $context) => ...);
$router->any('/:var1/:var2', static fn($var1, $var2, $context) => ...);

#--------------------------------------------------

// by variable function
$callback1 = function ($var1, $var2, $context) { ... };
$router->any('/:var1/:var2', $callback1);

$callback2 = static function ($var1, $var2, $context) { ... };
$router->any('/:var1/:var2', $callback2);

#--------------------------------------------------

// by class method
class AAA {
    public function method($var1, $var2, $context) { ... }
}
$aaa = new AAA();

$router->any('/:var1/:var2', "AAA::method"); // Call first constructor if exists and then method
$router->any('/:var1/:var2', [ AAA::class, 'method' ]); // Call first constructor if exists and then method
$router->any('/:var1/:var2', [ new AAA(), 'method' ]); // Call method
$router->any('/:var1/:var2', [ $aaa, 'method' ]); // Call method

#--------------------------------------------------

// by class static method
class BBB {
    public static function method($var1, $var2, $context) { ... }
}
$bbb = new BBB();

$router->any('/:var1/:var2', "BBB::method"); // Call static method
$router->any('/:var1/:var2', [ BBB::class, 'method' ]); // Call static method
$router->any('/:var1/:var2', [ new BBB(), 'method' ]); // Call static method
$router->any('/:var1/:var2', [ $bbb, 'method' ]); // Call static method

#--------------------------------------------------

// by class method with constructor
class CCC {
    public function __construct($context) { ... }
    public function method($var1, $var2, $context) { ... }
}

$router->any('/:var1/:var2', "CCC::method"); // Call first constructor and then method
$router->any('/:var1/:var2', [ CCC::class, "method" ]); // Call first constructor and then method

#--------------------------------------------------

// by class name/object
class DDD {
    public function __invoke($var1, $var2, $context) { ... }
}
$ddd = new DDD();

$router->any('/:var1/:var2', "DDD"); // Call first constructor if exists and then __invoke
$router->any('/:var1/:var2', DDD::class); // Call first constructor if exists and then __invoke
$router->any('/:var1/:var2', new DDD()); // Call __invoke
$router->any('/:var1/:var2', $ddd); // Call __invoke

#--------------------------------------------------

// by anonymous class, PHP 7+
$router->any('/:var1/:var2', new class {
    public function __invoke($var1, $var2, $context) { ... }
}); // Call __invoke
```