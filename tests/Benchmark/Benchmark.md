# Benchmark with leading public libraries
- [Aura Router](https://github.com/auraphp/Aura.Router)
- [Klein.php](https://github.com/klein/klein.php)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Pux PHP](https://github.com/c9s/Pux)
- [Symfony Routing](https://github.com/symfony/routing)

# Performed tests 
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
