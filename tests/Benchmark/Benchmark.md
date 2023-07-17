# Benchmark with leading public libraries

- [Aura Router](https://github.com/auraphp/Aura.Router)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Klein.php](https://github.com/klein/klein.php)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

## Result based on 100 random executions across 3000 routes with 5 variables

|                                   Library | Time               | Difference                        |
|------------------------------------------:|:-------------------|-----------------------------------|
|          **D5WHub Extend Router (4.2.0)** | **0.00012404680s** | **baseline**                      |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00041183233s     | 232% slower (+0.00028778553s)     |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00174865484s     | 1309.7% slower (+0.00162460804s)  |
| Symfony Routing (symfony/routing - 6.3.1) | 0.00541151285s     | 4262.5% slower (+0.00528746605s)  |
|           Klein.php (klein/klein - 2.1.2) | 0.01917615175s     | 15358.8% slower (+0.01905210495s) |
|         Aura Router (aura/router - 3.3.0) | 0.04351395607s     | 34978.7% slower (+0.04338990927s) |

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