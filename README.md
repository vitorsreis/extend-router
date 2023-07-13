# Very elegant, fast and powerful router for PHP
[![Latest Stable Version](https://img.shields.io/packagist/v/d5whub/extend-router?style=flat-square&label=stable&color=2E9DD3)](https://packagist.org/packages/d5whub/extend-router)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/d5whub/extend-router/php?style=flat-square&color=777BB3)](https://packagist.org/packages/d5whub/extend-router)
[![License](https://img.shields.io/packagist/l/d5whub/extend-router?style=flat-square&color=418677)](https://packagist.org/packages/d5whub/extend-router)
[![Total Downloads](https://img.shields.io/packagist/dt/d5whub/extend-router?style=flat-square&color=0476B7)](https://packagist.org/packages/d5whub/extend-router)
[![Repo Stars](https://img.shields.io/github/stars/d5whub/extend-router?style=social)](https://github.com/d5whub/extend-router)

Indexing by words tree and regex marked, this router is very elegant, fast and powerful. Architected as a queue of merged middlewares (not unique match), it proposes multiple interactions in routes with cache, contexts and persistent data.
Unit test passed versions: ```5.6```, ```7.4```, ```8.1``` and ```8.2```

---

## Benchmark
Check out benchmark with leading public libraries [here](/tests/Benchmark/Benchmark.md).

---

## Install
```shell
composer require "d5whub/extend-router"
```

## Usage
```php
use D5WHUB\Extend\Router\Router;

$router = new Router();
$router->get('/', function () { echo "hello word"; });
$router->get('/product/:id', function ($id) { echo "product $id"; });    
$router->match('GET', '/product/100')->execute();
// output: "product 100"
```

---

### With cache

```php
$cache = new \D5WHUB\Extend\Router\Cache\Memory();
#$cache = new \D5WHUB\Extend\Router\Cache\Apcu();
#$cache = new \D5WHUB\Extend\Router\Cache\File();
#$cache = new \D5WHUB\Extend\Router\Cache\Memcache();
#$cache = new \D5WHUB\Extend\Router\Cache\Memcached();
#$cache = new \D5WHUB\Extend\Router\Cache\Redis();

$cache->clear(); # if change routes
$router = new Router($cache);
```

---

### Router group
You can add group of routes:
```php
$router->group('/product', function (D5WHUB\Extend\Router\Router $router) {
    $router->get('/:id', function ($id) { echo "get product $id"; });
    $router->post('/:id', function ($id) { echo "post product $id"; });
});
$router->match('POST', '/product/100')->execute();
// output: "post product 100"
```

---

### Context param
Context contains all information of current execution, use argument with name "$context" of type omitted, "mixed" or "\D5WHUB\Extend\Router\Context" on middlewares or on constructor of class if middleware of type class method non-static.
```php
$router->get('/aaa', function ($context) { ... });
$router->any('/aaa', function (mixed $context) { ... }); # Explicit mixed type only PHP 8+
$router->get('/a*', function (D5WHUB\Extend\Router\Context $context) { ... });
```

---

### Friendly uris
You can add friendly url to redirect to specific routes:
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

### Middleware with arguments
Route variables and context param are not mandatory in callbacks, so they can be omitted without problems.
```php
$router->any('/:var1/:var2', function () { ... });
$router->any('/:var1/:var2', function ($var1) { ... });
$router->any('/:var1/:var2', function ($var2) { ... });
$router->any('/:var1/:var2', function ($context) { ... });
$router->any('/:var1/:var2', function (D5WHUB\Extend\Router\Context $custom_name_context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2) { ... });
$router->any('/:var1/:var2', function ($var1, $context) { ... });
$router->any('/:var1/:var2', function ($var1, D5WHUB\Extend\Router\Context $custom_name_context) { ... });
$router->any('/:var1/:var2', function ($var2, $context) { ... });
$router->any('/:var1/:var2', function ($var2, D5WHUB\Extend\Router\Context $custom_name_context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2, $context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2, D5WHUB\Extend\Router\Context $custom_name_context) { ... });
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
            "{$context->current->httpMethod} {$context->current->route} = $context->result" .
         '], ';
});
// output: "[1/3 POST /:aa = a1:99], [2/3 POST /:aa = a2:99], [3/3 POST /:bb = bb:99], "
```

---

### Persisting data
You can persist data in context so that it is persisted in future callbacks.
```php
use D5WHUB\Extend\Router\Context;

$router->get('/aaa', function (Context $context) {
    $context->set('xxx', $context->get('xxx', 0) + 10); # 2. Increment value: 15
});
$router->get('/var2', function (Context $context) {
    $context->set('xxx', $context->get('xxx', 0) + 15); # 3. Increment value: 30
});
$context = $router->match('GET', '/aaa')
    ->set('xxx', 5) # 1. Initial value: 5
    ->execute();

echo $context->get('xxx');
// output: "30"
```

---

### Merge callbacks
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
use D5WHUB\Extend\Router\Context;

$router->get('/aaa', function () { echo "11 "; }, function () { echo "12 "; }, function () { echo "13 "; });
$router->any('/aaa', function () { echo "2 "; });
$router->get('/a*', function () { echo "3 "; });
$router->any('/a*', function (Context $context) { echo "4 "; $context->stop(); });
$router->get('*', function () { echo "5 "; });
$router->any('*', function () { echo "6 "; });
$router->get('/:var', function ($var) { echo "7 "; });
$router->any('/:var', function ($var) { echo "8 "; });
$router->match('GET', '/aaa')->execute();
// output: "11 12 13 2 3 4 "
```

---

### Supported callback types
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
$router->any('/:var1/:var2', fn($var1, $var2, $context) => { ... });
$router->any('/:var1/:var2', static fn($var1, $var2, $context) => { ... });

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
$ccc = new CCC();

$router->any('/:var1/:var2', "CCC::method" ]); // Call first constructor and then method
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

---

### Context methods/properties
| Property                                   | Description                                                                        |
|:-------------------------------------------|:-----------------------------------------------------------------------------------|
| ```$context->current->route```             | Current match middleware route                                                     |
| ```$context->current->httpMethod```        | Current match middleware http method                                               |
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
| ```$context->result```                     | Partial/Final execution result                                                     |
| ```$context->execute(?$callback)```        | Start execution, ```$callback``` is optional and available argument ```$context``` |
| ```$context->stop()```                     | Stop execution                                                                     |
| ```$context->get($key, $default = null)``` | Get persistent data                                                                |
| ```$context->set($key, $value)```          | Set persistent data                                                                |
| ```$context->has($key)```                  | Check if persistent data exists                                                    |
