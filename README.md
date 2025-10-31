# Symfony AOP Coroutine Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![Build Status](https://img.shields.io/travis/tourze/symfony-aop-coroutine-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/symfony-aop-coroutine-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/symfony-aop-coroutine-bundle)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/symfony-aop-coroutine-bundle/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![License](https://img.shields.io/packagist/l/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)

A Symfony bundle that provides coroutine-based service isolation using AOP (Aspect-Oriented Programming), enabling each request context to have its own independent service instances for better state isolation and concurrent processing.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
  - [Basic Usage](#basic-usage)
- [Built-in Coroutine Services](#built-in-coroutine-services)
  - [Request & Routing Services](#request-routing-services)
  - [Template Engine](#template-engine)
  - [Security Services](#security-services)
  - [Logging Services](#logging-services)
  - [Other Services](#other-services)
- [Advanced Configuration](#advanced-configuration)
  - [Alternative Ways to Enable Coroutine](#alternative-ways-to-enable-coroutine)
  - [Enhanced Logging](#enhanced-logging)
- [Important Notes](#important-notes)
- [How It Works](#how-it-works)
- [Testing](#testing)
- [Contributing](#contributing)
  - [Development Setup](#development-setup)
- [License](#license)
- [Changelog](#changelog)

## Features

- **AOP-based service isolation**: Uses Aspect-Oriented Programming to intercept service calls and provide isolated instances
- **Simple annotation-based configuration**: Mark services as coroutine-enabled with the `#[AsCoroutine]` attribute  
- **Built-in support for common services**: Automatic coroutine support for request_stack, twig, monolog, Doctrine EntityManager, session handlers, and more
- **Automatic context management**: Handles context creation and cleanup for each request lifecycle
- **Enhanced logging**: Automatically adds coroutine context ID to all log records for better traceability
- **Memory efficient**: Only creates isolated instances when needed, with automatic cleanup

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- `tourze/symfony-aop-bundle` for AOP functionality
- `tourze/symfony-runtime-context-bundle` for context management

## Installation

Install via Composer:

```bash
composer require tourze/symfony-aop-coroutine-bundle
```

Register the bundle in `config/bundles.php`:

```php
<?php

return [
    // ... other bundles
    Tourze\Symfony\AopCoroutineBundle\AopCoroutineBundle::class => ['all' => true],
];
```

## Quick Start

### Basic Usage

1. **Mark your service as coroutine-enabled** using the `#[AsCoroutine]` attribute:

```php
<?php

use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class UserService
{
    private array $userData = [];
    
    public function setUserData(string $userId, array $data): void 
    {
        $this->userData[$userId] = $data;
    }
    
    public function getUserData(string $userId): ?array 
    {
        return $this->userData[$userId] ?? null;
    }
}
```

2. **Use the service in your controller** - each request will have its own isolated state:

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/api/user/{id}', methods: ['POST'])]
    public function updateUser(string $id, UserService $userService): JsonResponse
    {
        // Each request gets its own UserService instance
        $userService->setUserData($id, ['name' => 'John', 'email' => 'john@example.com']);
        
        return new JsonResponse(['status' => 'updated']);
    }
}
```

## Built-in Coroutine Services

The following services are automatically coroutine-enabled by default:

### Request & Routing Services
- `request_stack` - Symfony's request stack
- `session_listener` - Session event listener
- `router_listener` - Router event listener

### Template Engine
- `twig` - Twig template engine
- `twig.loader.native_filesystem` - Twig filesystem loader

### Security Services
- `security.untracked_token_storage` - Security token storage
- `security.csrf.token_storage` - CSRF token storage

### Logging Services
- `monolog.logger` - Main Monolog logger
- `monolog.logger.*` - All named Monolog loggers

### Other Services
- `debug.stopwatch` - Symfony debug stopwatch
- All services implementing `SessionHandlerInterface`
- All services implementing `SessionUpdateTimestampHandlerInterface`
- All services implementing `Doctrine\ORM\EntityManagerInterface`
- Services tagged with `as-coroutine`

## Advanced Configuration

### Alternative Ways to Enable Coroutine

Besides the `#[AsCoroutine]` attribute, you can also enable coroutine for services through:

1. **Service Tags** - Tag your service with `as-coroutine`:

```yaml
# config/services.yaml
services:
    App\Service\MyService:
        tags: ['as-coroutine']
```

2. **Interface Implementation** - Services implementing these interfaces are automatically coroutine-enabled:
    - `SessionHandlerInterface`
    - `SessionUpdateTimestampHandlerInterface`
    - `Doctrine\ORM\EntityManagerInterface`

### Enhanced Logging

The bundle automatically enhances logging by adding a `context_id` field to all log records, making it easier to trace requests across coroutine contexts:

```php
// Your logs will automatically include context_id
$logger->info('User updated', ['user_id' => 123]);
// Result: [2023-01-01 10:00:00] app.INFO: User updated {"user_id":123,"context_id":"ctx_abc123"}
```

## Important Notes

- **Memory Usage**: Coroutine services consume more memory as each context maintains separate instances. Only use for services that require state isolation.
- **Method Limitations**: Methods returning `static` are not supported due to proxy limitations.
- **Dependency Management**: Avoid circular dependencies in coroutine services.
- **Singleton Services**: Not recommended for services that should maintain global state.

## How It Works

The bundle uses AOP (Aspect-Oriented Programming) to intercept service instantiation and method calls. When a coroutine-enabled service is accessed:

1. The `CoroutineAspect` intercepts the service call
2. It checks if an instance exists for the current context
3. If not, it creates a new isolated instance using the `InstanceService`
4. The instance is stored per context and automatically cleaned up when the request terminates

## Testing

Run the test suite to ensure everything is working correctly:

```bash
# Run all tests
./vendor/bin/phpunit packages/symfony-aop-coroutine-bundle/tests

# Run PHPStan analysis
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/symfony-aop-coroutine-bundle
```

## Contributing

We welcome contributions! Please follow these guidelines:

1. **Submit Issues** for bugs, feature requests, or questions
2. **Create Pull Requests** with your improvements
3. **Follow PSR standards** for code style and formatting
4. **Run tests** before submitting PRs to ensure compatibility
5. **Update documentation** if your changes affect usage

### Development Setup

```bash
# Clone the repository
git clone https://github.com/tourze/symfony-aop-coroutine-bundle.git

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit packages/symfony-aop-coroutine-bundle/tests
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

**Author**: tourze team

## Changelog

See the Git commit history for version updates and important changes.
