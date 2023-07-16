# Benchmark with leading public libraries
- [Aura Router](https://github.com/auraphp/Aura.Router)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Klein.php](https://github.com/klein/klein.php)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

## Result based on 100 random executions across 3000 routes with 5 variables
|                                   Library | Time               | Difference                        |
|------------------------------------------:|:-------------------|-----------------------------------|
|          **D5WHub Extend Router (4.1.1)** | **0.00011146784s** | **baseline**                      |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00040563345s     | 263.9% slower (+0.00029416561s)   |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00156821966s     | 1306.9% slower (+0.00145675182s)  |
| Symfony Routing (symfony/routing - 6.3.1) | 0.00455891132s     | 3989.9% slower (+0.00444744349s)  |
|           Klein.php (klein/klein - 2.1.2) | 0.01843150377s     | 16435.3% slower (+0.01832003593s) |
|         Aura Router (aura/router - 3.3.0) | 0.04173009872s     | 37336.9% slower (+0.04161863089s) |

## Achievable tests 
- Instance create
- Matching random routes
- Matching first route
- Matching last route
- Matching not found route
- Matching first route with method not allowed
- Matching last route with method not allowed

### Requirements
- PHP 8.2 or higher

### Install dependencies
On folder `tests/Benchmark`, run:
```shell
composer install --ignore-platform-reqs
```

### Run benchmark

##### Opt 1: Benchmark on console, go to folder `tests/Benchmark` and run:
```shell
php benchmark-execute.php
```

##### Opt 2: Benchmark on browser, go to folder `tests/Benchmark` and run:
```shell
php -S 127.0.0.1:80
start http://127.0.0.1:80/benchmark-execute.php
```