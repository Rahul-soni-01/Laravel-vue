<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Project

- Framework: laravel 9.*
- PHP 8.0^
- API Authentication : JWT (composer require tymon/jwt-auth --ignore-platform-reqs)
- Design pattern : repository pattern, service
- Mysql
- AWS

## Install Project

- clone source API :
- composer install --ignore-platform-reqs
- create file env
- php artisan key:generate
- config host and DB
- php artisan migrate
- php artisan db:seed || php artisan db:seed --class={classSeeder}

- JWT
    - composer require tymon/jwt-auth --ignore-platform-reqs
    - php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
    - php artisan jwt:secret
  
## Clear Cache

- php artisan cache:clear
- php artisan route:cache
- php artisan view:clear
- php artisan config:cache

## autoload
- php artisan clear-compiled
- composer dump-autoload
- php artisan optimize
