# Benchmark with leading public libraries

- [Aura Router](https://github.com/auraphp/Aura.Router)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Klein.php](https://github.com/klein/klein.php)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

## Result based on 100 random executions across 3000 routes with 5 variables

|                                   Library | Time               | Difference                        |
|------------------------------------------:|:-------------------|-----------------------------------|
|             **vsr extend router (4.3.0)** | **0.00012993336s** | **baseline**                      |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00042273283s     | 225.3% slower (+0.00029279947s)   |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00169231653s     | 1202.4% slower (+0.00156238317s)  |
| Symfony Routing (symfony/routing - 6.3.1) | 0.00466184855s     | 3487.9% slower (+0.00453191519s)  |
|           Klein.php (klein/klein - 2.1.2) | 0.01854341269s     | 14171.5% slower (+0.01841347933s) |
|         Aura Router (aura/router - 3.3.0) | 0.04323968887s     | 33178.4% slower (+0.04310975552s) |

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