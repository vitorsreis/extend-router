# Benchmark with leading public libraries

- [Aura Router](https://github.com/auraphp/Aura.Router)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Klein.php](https://github.com/klein/klein.php)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

## Result based on 10 random executions across 3000 routes with 5 variables

|                                   Library | Time               | Difference                       |
|------------------------------------------:|:-------------------|----------------------------------|
|             **vsr extend router (4.5.0)** | **0.00040967464s** | **baseline**                     |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00536572933s     | 1209.8% slower (+0.00495605469s) |
|           Pux PHP (corneltek/pux - 1.6.1) | 0.01021926403s     | 2394.5% slower (+0.00980958939s) |
|           Klein.php (klein/klein - 2.1.2) | 0.02873334885s     | 6913.7% slower (+0.02832367420s) |
| Symfony Routing (symfony/routing - 6.3.1) | 0.03280546665s     | 7907.7% slower (+0.03239579201s) |
|         Aura Router (aura/router - 3.3.0) | 0.07312681675s     | 17750% slower (+0.07271714211s)  |

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