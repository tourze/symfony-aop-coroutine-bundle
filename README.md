# AopCoroutineBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![Build Status](https://img.shields.io/travis/tourze/symfony-aop-coroutine-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/symfony-aop-coroutine-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/symfony-aop-coroutine-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)

A Symfony bundle providing coroutine-based service isolation via AOP, enabling each request context to have its own independent service instances.

## Features

- Coroutine-based service isolation using AOP
- Easily mark services as coroutine-enabled with the `#[AsCoroutine]` attribute
- Built-in coroutine support for common services (request_stack, twig, monolog, etc.)
- Automatic context management for each request, with resource cleanup
- Enhanced logging with coroutine context ID

## Installation

- Requires PHP 8.1+
- Requires Symfony 6.4+

Install via Composer:

```bash
composer require tourze/symfony-aop-coroutine-bundle
```

Register the bundle in `config/bundles.php`:

```php
return [
    Tourze\Symfony\AopCoroutineBundle\AopCoroutineBundle::class => ['all' => true],
];
```

## Quick Start

1. Mark your service as coroutine-enabled:

```php
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class UserService
{
    private $state;
    public function setState($state) { $this->state = $state; }
}
```

2. Inject and use the service in your controller. Each request will have its own isolated state.

## Built-in Coroutine Services

The following services are coroutine-enabled by default:

- Request-related: `request_stack`, `session_listener`, `router_listener`
- Twig: `twig`, `twig.loader.native_filesystem`
- Security: `security.untracked_token_storage`, `security.csrf.token_storage`
- Logging: `monolog.logger`, `monolog.logger.*`
- Others: `debug.stopwatch`, Doctrine EntityManager, Session Handler

## Advanced Usage & Configuration

- You can enable coroutine for additional services via service tags, inheritance, or the `#[AsCoroutine]` attribute
- Logging processor automatically adds `context_id` to all log records
- Full support for coroutine-enabled Doctrine EntityManager and Session Handler

## Usage Notes

- Coroutine services consume more memory; only use for services that require state isolation
- Methods returning `static` are not supported; singleton services are not recommended
- Avoid circular dependencies in coroutine services

## Contribution Guide

- Contributions are welcome via Issues and PRs
- Code style should follow PSR standards; please run tests before submitting PRs
- Run tests with:

```bash
./vendor/bin/phpunit packages/symfony-aop-coroutine-bundle/tests
```

## License

- MIT License
- Author: tourze team

## Changelog

See CHANGELOG or Git history for version updates and important changes.
