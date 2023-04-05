# Very elegant, fast and powerful router for PHP 8.2
[![Latest Stable Version](https://poser.pugx.org/d5whub/extend-router/v/stable)](https://packagist.org/packages/d5whub/extend-router)
[![PHP Version Require](http://poser.pugx.org/d5whub/extend-router/require/php)](https://packagist.org/packages/d5whub/extend-router)
[![Monthly Downloads](https://poser.pugx.org/d5whub/extend-router/d/monthly)](https://packagist.org/packages/d5whub/extend-router)
[![Total Downloads](https://poser.pugx.org/d5whub/extend-router/downloads)](https://packagist.org/packages/d5whub/extend-router)
[![License](https://poser.pugx.org/d5whub/extend-router/license)](https://packagist.org/packages/d5whub/extend-router)

Indexing by words tree and regex marked, this router is very elegant, fast and powerful. Architected as a queue of merged middlewares (not unique match), it proposes multiple interactions in routes with cache, contexts and persistent data.

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
use D5WHUB\Extend\Router\Cache\Memory;

$router = new Router(new Memory());
$router->get('/', function () { echo "hello word" });
$router->get('/product/:id|d', function ($id) { echo "page product $id" });    
$router->match('GET', '/product/100')->execute(); // output: "page product 100"
```

---

### Context param
Context contains all information of current execution, use argument with name "$context" of type omitted, "mixed" or "\D5WHUB\Extend\Router\Context" on middlewares or on constructor of class if middleware of type class method non-static.
```php
$router->get('/aaa', function ($context) { });
$router->any('/aaa', function (mixed $context) { });
$router->get('/a*', function (D5WHUB\Extend\Router\Context $context) { });
```

---

### Friendly uris
You can add friendly url to redirect to specific routes:
```php
$router->post('/product/:id|d', function ($id) { echo "save product $id" });
$router->friendly('/iphone', '/product/100');
$router->match('POST', '/iphone')->execute(); // output: "save product 100"
```

---

### Filters
Filters are used to add regex to route variables in a nicer and cleaner way.

```php
$router->get('/:var1|09', fn($var1) => "FILTER[09] : $var1");
$router->get('/:var1|az', fn($var1) => "FILTER[az] : $var1");

$router->match('GET', '/111')->execute(); // output: "FILTER[09] : 111"
$router->match('GET', '/aaa')->execute(); // output: "FILTER[az] : aaa"
```

You can add custom filters:
```php
$router->addFilter('only_numeric', '\d+')
$router->get('/:var1|only_numeric', fn() => 'CUSTOM_FILTER');

$router->addFilter('w10', '\w{10}')
$router->get('/:var1|w10', fn() => 'CUSTOM_FILTER');
```

Below are pre-registered filters:

|            Key             | Regex                                                                           |
|:--------------------------:|---------------------------------------------------------------------------------|
| _&lt;omitted or empty&gt;_ | \[^\/]+                                                                         |
|             09             | \[0-9]+                                                                         |
|             az             | \[a-z]+                                                                         |
|             AZ             | \[A-Z]+                                                                         |
|             aZ             | \[a-zA-Z]+                                                                      |
|             d              | \d+                                                                             |
|             d              | \d+                                                                             |
|             D              | \D+                                                                             |
|             w              | \w+                                                                             |
|             W              | \W+                                                                             |
|            uuid            | \[0-9a-f]{8}-\[0-9a-f]{4}-\[0-5]\[0-9a-f]{3}-\[089ab]\[0-9a-f]{3}-\[0-9a-f]{12} |
---

### Callback with arguments
Route variables and context param are not mandatory in callbacks, so they can be omitted without problems.
```php
$router->any('/:var1/:var2', function () { ... });
$router->any('/:var1/:var2', function ($var1) { ... });
$router->any('/:var1/:var2', function ($var2) { ... });
$router->any('/:var1/:var2', function ($context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2) { ... });
$router->any('/:var1/:var2', function ($var1, $context) { ... });
$router->any('/:var1/:var2', function ($var2, $context) { ... });
$router->any('/:var1/:var2', function ($var1, $var2, $context) { ... });
```

---

### Persisting data
You can persist data in context so that it is persisted in future callbacks.
```php
use D5WHUB\Extend\Router\Context;

$router->get('/aaa', function (Context $context) {
    $context->set('xxx', $context->get('xxx', 0) + 10);
});
$router->get('/var2', function (Context $context) {
    $context->set('xxx', $context->get('xxx', 0) + 10);
});
$context = $router->match('GET', '/aaa')
    ->set('xxx', 1)
    ->execute();

echo $context->get('xxx'); // output: "21"
```

---

### Merge callbacks
With the "not unique match" pattern, you can have multiple callbacks in queue per order of addition for an uri.
```php
$router->get('/aaa', function () { echo "1 "; }, function () { echo "1 "; }, function () { echo "1 "; });
$router->any('/aaa', function () { echo "2 "; });
$router->get('/a*', function () { echo "3 "; });
$router->any('/a*', function () { echo "4 "; });
$router->get('*', function () { echo "5 "; });
$router->any('*', function () { echo "6 "; });
$router->get('/:var', function ($var) { echo "7 "; });
$router->any('/:var', function ($var) { echo "8 "; });
$router->match('GET', '/aaa')->execute(); // output: "1 1 1 2 3 4 5 6 7 8 "
```

You can stop the queue using "stop" method of context
```php
use D5WHUB\Extend\Router\Context;

$router->get('/aaa', function () { echo "1 "; }, function () { echo "1 "; }, function () { echo "1 "; });
$router->any('/aaa', function () { echo "2 "; });
$router->get('/a*', function () { echo "3 "; });
$router->any('/a*', function (Context $context) { echo "4 "; $context->stop(); });
$router->get('*', function () { echo "5 "; });
$router->any('*', function () { echo "6 "; });
$router->get('/:var', function ($var) { echo "7 "; });
$router->any('/:var', function ($var) { echo "8 "; });
$router->match('GET', '/aaa')->execute(); // output: "1 1 1 2 3 4 "
```

---

### Supported callback types
```php
// by native function name
$router->get('/:haystack/:needle', "stripos");

#--------------------------------------------------

// by function name
function callback($var1, $var2, $context) { ... }
$router->get('/:var1/:var2', "callback");

#--------------------------------------------------

// by anonymous function
$router->any('/:var1/:var2', function ($var1, $var2, $context) { ... });

#--------------------------------------------------

// by arrow function
$router->any('/:var1/:var2', fn($var1, $var2, $context) => { ... });

#--------------------------------------------------

// by variable function
$callback = function ($var1, $var2, $context) { ... };
$router->any('/:var1/:var2', $callback);

#--------------------------------------------------

// by class method
class AAA {
    public function method($var1, $var2, $context) { ... }
}
$aaa = new BBB();

$router->any('/:var1/:var2', "AAA::method");
$router->any('/:var1/:var2', [ AAA::class, 'method' ]);
$router->any('/:var1/:var2', [ new AAA(), 'method' ]);
$router->any('/:var1/:var2', [ $aaa, 'method' ]);

#--------------------------------------------------

// by class static method
class BBB {
    public static function method($var1, $var2, $context) { ... }
}
$bbb = new BBB();

$router->any('/:var1/:var2', "BBB::method");
$router->any('/:var1/:var2', [ BBB::class, 'method' ]);
$router->any('/:var1/:var2', [ new BBB(), 'method' ]);
$router->any('/:var1/:var2', [ $bbb, 'method' ]);

#--------------------------------------------------

// by class method with constructor
class CCC {
    public function __construct($context) { ... }
    public function method($var1, $var2, $context) { ... }
}
$ccc = new DDD();

$router->any('/aaa', [ CCC::class, "method" ]);
$router->any('/aaa', "CCC::method" ]);
$router->any('/aaa', [ $ccc, "method" ]);

#--------------------------------------------------

// by class name/object
class DDD {
    public function __invoke($var1, $var2, $context) { ... }
}
$ddd = new DDD();

$router->any('/:var1/:var2', "DDD");
$router->any('/:var1/:var2', DDD::class);
$router->any('/:var1/:var2', new DDD());
$router->any('/:var1/:var2', $ddd);

#--------------------------------------------------

// by anonymous class
$router->any('/:var1/:var2', new class {
    public function __invoke($var1, $var2, $context) { ... }
});
```
