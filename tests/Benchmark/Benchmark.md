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

## Install and execute

##### On folder `tests/Benchmark`, run:
```shell
composer install --ignore-platform-reqs
```

##### Benchmark on console, go to folder `tests/Benchmark` and run:
```shell
php benchmark-execute.php
```

##### Benchmark on browser, go to folder `tests/Benchmark` and run:
```shell
php -S 127.0.0.1:80
start http://127.0.0.1:80/benchmark-execute.php
```

---

## Average results
|                                   Library | Time               | Difference                       |
|------------------------------------------:|:-------------------|----------------------------------|
|          **D5WHub Extend Router (3.0.0)** | **0.00015154839s** | **baseline**                     |
|      FastRoute (nikic/fast-route - 1.3.0) | 0.00024784088s     | 63.5% slower (+0.00009629250s)   |
|           Pux PHP (corneltek/pux - 1.6.0) | 0.00081560135s     | 438.2% slower (+0.00066405296s)  |
| Symfony Routing (symfony/routing - 6.2.8) | 0.00202444553s     | 1235.8% slower (+0.00187289715s) |
|           Klein.php (klein/klein - 2.1.2) | 0.00907448292s     | 5887.8% slower (+0.00892293453s) |
|         Aura Router (aura/router - 3.1.0) | 0.01434703827s     | 9367% slower (+0.01419548988s)   |
