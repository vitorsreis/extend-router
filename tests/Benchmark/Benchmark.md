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
|          **D5WHub Extend Router (4.0.0)** | **0.00012229443s** | **baseline**                      |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00043141603s     | 252.8% slower (+0.00030912161s)   |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00174220800s     | 1324.6% slower (+0.00161991358s)  |
| Symfony Routing (symfony/routing - 6.3.1) | 0.00515930414s     | 4118.8% slower (+0.00503700972s)  |
|           Klein.php (klein/klein - 2.1.2) | 0.02080127001s     | 16909.2% slower (+0.02067897558s) |
|         Aura Router (aura/router - 3.3.0) | 0.04540429592s     | 37027% slower (+0.04528200150s)   |