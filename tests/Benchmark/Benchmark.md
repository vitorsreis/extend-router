# Benchmark with leading public libraries

- [Aura Router](https://github.com/auraphp/Aura.Router)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Klein.php](https://github.com/klein/klein.php)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

## Result based on 100 random executions across 3000 routes with 5 variables

|                                   Library | Time               | Difference                        |
|------------------------------------------:|:-------------------|-----------------------------------|
|          **D5WHub Extend Router (4.2.0)** | **0.00014428377s** | **baseline**                      |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00069982767s     | 385% slower (+0.00055554390s)     |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00255300045s     | 1669.4% slower (+0.00240871668s)  |
| Symfony Routing (symfony/routing - 6.3.1) | 0.00501614332s     | 3376.6% slower (+0.00487185955s)  |
|           Klein.php (klein/klein - 2.1.2) | 0.01947408438s     | 13397.1% slower (+0.01932980061s) |
|         Aura Router (aura/router - 3.3.0) | 0.04739187002s     | 32746.3% slower (+0.04724758625s) |

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