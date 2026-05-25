<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## О Laravel

Laravel - это фреймворк для веб-приложений с выразительным, элегантным синтаксисом. Мы считаем, что разработка должна быть приятным и творческим процессом, чтобы приносить истинное удовлетворение.

## О проекте

![Превью приложения](preview.png)

Данный сервис показывает статистику посещенных сайтов. Скрипт прикрепляется к любому сайту и производит отправку запроса на текущий сервис, который собирает информацию: IP, геоданные, user agent, время посещения.

Встраиваемый скрипт предоставляется на главной странице сервиса.

### Архитектура обработки данных

При обработке данных система использует один из двух сервисов в зависимости от доступной информации:

- **OpenStreetMap** — используется, если переданы координаты (широта и долгота)
- **2IP** — используется, если координаты отсутствуют и нужно определить геоданные по IP

## Настройка

В файле `.env` можно указать токен сервиса 2IP (`2IP_TOKEN`). Если токен не указан, вызов 2IP будет пропущен с предупреждением в логах.

Для локального тестирования предусмотрен тестовый IP. В локальной среде он используется вместо реального IP посетителя, так как в противном случае сервис будет отправлять внутренний IP адрес.

## Запуск

```bash
docker-compose up
docker exec analytics_app php artisan db:seed
```
