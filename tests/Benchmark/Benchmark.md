# Benchmark with leading public libraries
- [Aura Router](https://github.com/auraphp/Aura.Router)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Klein.php](https://github.com/klein/klein.php)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

## Performed tests 
- Create instances
- Matching last route
- Matching not found route
- Matching first route with method not allowed
- Matching last route with method not allowed
- Matching random routes

---

## Requirements
- PHP 8.2 or higher

## Install dependencies

##### On folder `tests/Benchmark`, run:
```shell
composer install --ignore-platform-reqs
```

## Run benchmark

##### Opt 1: Benchmark on console, go to folder `tests/Benchmark` and run:
```shell
php benchmark-execute.php
```

##### Opt 2: Benchmark on browser, go to folder `tests/Benchmark` and run:
```shell
php -S 127.0.0.1:80
start http://127.0.0.1:80/benchmark-execute.php
```

---

## Matching random 100 times, 3000 routes with 5 variables
|                                   Library | Time               | Difference                        |
|------------------------------------------:|:-------------------|-----------------------------------|
|          **D5WHub Extend Router (4.1.1)** | **0.00011346817s** | **baseline**                      |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00040574789s     | 257.6% slower (+0.00029227972s)   |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00160671949s     | 1316% slower (+0.00149325132s)    |
| Symfony Routing (symfony/routing - 6.3.1) | 0.00492270231s     | 4238.4% slower (+0.00480923414s)  |
|           Klein.php (klein/klein - 2.1.2) | 0.01885573149s     | 16517.6% slower (+0.01874226332s) |
|         Aura Router (aura/router - 3.3.0) | 0.04172217846s     | 36669.9% slower (+0.04160871029s) |