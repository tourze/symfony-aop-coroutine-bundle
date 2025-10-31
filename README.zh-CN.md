# Symfony AOP 协程支持包

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![Build Status](https://img.shields.io/travis/tourze/symfony-aop-coroutine-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/symfony-aop-coroutine-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/symfony-aop-coroutine-bundle)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/symfony-aop-coroutine-bundle/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![License](https://img.shields.io/packagist/l/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)

基于 Symfony 的协程支持包，通过 AOP（面向切面编程）技术实现服务的协程化，使每个请求上下文拥有独立的服务实例，提供更好的状态隔离和并发处理能力。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [快速开始](#快速开始)
  - [基本用法](#基本用法)
- [内置协程服务](#内置协程服务)
  - [请求与路由服务](#请求与路由服务)
  - [模板引擎](#模板引擎)
  - [安全服务](#安全服务)
  - [日志服务](#日志服务)
  - [其他服务](#其他服务)
- [高级配置](#高级配置)
  - [启用协程的其他方式](#启用协程的其他方式)
  - [增强日志](#增强日志)
- [重要注意事项](#重要注意事项)
- [工作原理](#工作原理)
- [测试](#测试)
- [贡献](#贡献)
  - [开发环境设置](#开发环境设置)
- [许可证](#许可证)
- [更新日志](#更新日志)

## 功能特性

- **基于 AOP 的服务隔离**：使用面向切面编程技术拦截服务调用并提供隔离实例
- **简单的注解配置**：通过 `#[AsCoroutine]` 注解轻松标记服务为协程服务
- **常用服务内置支持**：自动支持 request_stack、twig、monolog、Doctrine EntityManager、会话处理器等常用服务的协程化
- **自动上下文管理**：处理每个请求生命周期的上下文创建和清理
- **日志增强**：自动为所有日志记录添加协程上下文 ID，提供更好的可追踪性
- **内存高效**：仅在需要时创建隔离实例，并自动清理

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- `tourze/symfony-aop-bundle` 提供 AOP 功能
- `tourze/symfony-runtime-context-bundle` 提供上下文管理

## 安装

通过 Composer 安装：

```bash
composer require tourze/symfony-aop-coroutine-bundle
```

在 `config/bundles.php` 中注册 Bundle：

```php
<?php

return [
    // ... 其他 bundle
    Tourze\Symfony\AopCoroutineBundle\AopCoroutineBundle::class => ['all' => true],
];
```

## 快速开始

### 基本用法

1. **使用 `#[AsCoroutine]` 注解标记服务为协程服务**：

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

2. **在控制器中使用服务** - 每个请求都有自己的隔离状态：

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
        // 每个请求都获得自己的 UserService 实例
        $userService->setUserData($id, ['name' => 'John', 'email' => 'john@example.com']);
        
        return new JsonResponse(['status' => 'updated']);
    }
}
```

## 内置协程服务

以下服务默认自动启用协程支持：

### 请求与路由服务
- `request_stack` - Symfony 请求栈
- `session_listener` - 会话事件监听器
- `router_listener` - 路由事件监听器

### 模板引擎
- `twig` - Twig 模板引擎
- `twig.loader.native_filesystem` - Twig 文件系统加载器

### 安全服务
- `security.untracked_token_storage` - 安全令牌存储
- `security.csrf.token_storage` - CSRF 令牌存储

### 日志服务
- `monolog.logger` - 主要 Monolog 日志器
- `monolog.logger.*` - 所有命名的 Monolog 日志器

### 其他服务
- `debug.stopwatch` - Symfony 调试秒表
- 所有实现 `SessionHandlerInterface` 的服务
- 所有实现 `SessionUpdateTimestampHandlerInterface` 的服务
- 所有实现 `Doctrine\ORM\EntityManagerInterface` 的服务
- 标记为 `as-coroutine` 的服务

## 高级配置

### 启用协程的其他方式

除了 `#[AsCoroutine]` 注解，您还可以通过以下方式为服务启用协程：

1. **服务标签** - 为您的服务添加 `as-coroutine` 标签：

```yaml
# config/services.yaml
services:
    App\Service\MyService:
        tags: ['as-coroutine']
```

2. **接口实现** - 实现以下接口的服务会自动启用协程：
    - `SessionHandlerInterface`
    - `SessionUpdateTimestampHandlerInterface`
    - `Doctrine\ORM\EntityManagerInterface`

### 增强日志

该包会自动为所有日志记录添加 `context_id` 字段，使跨协程上下文的请求追踪更加容易：

```php
// 您的日志会自动包含 context_id
$logger->info('用户已更新', ['user_id' => 123]);
// 结果: [2023-01-01 10:00:00] app.INFO: 用户已更新 {"user_id":123,"context_id":"ctx_abc123"}
```

## 重要注意事项

- **内存使用**：协程服务会消耗更多内存，因为每个上下文都维护独立的实例。仅对需要状态隔离的服务使用。
- **方法限制**：由于代理限制，不支持返回 `static` 的方法。
- **依赖管理**：避免协程服务中的循环依赖。
- **单例服务**：不推荐用于应该维护全局状态的服务。

## 工作原理

该包使用 AOP（面向切面编程）来拦截服务实例化和方法调用。当访问启用协程的服务时：

1. `CoroutineAspect` 拦截服务调用
2. 检查当前上下文是否存在实例
3. 如果不存在，使用 `InstanceService` 创建新的隔离实例
4. 实例按上下文存储，并在请求终止时自动清理

## 测试

运行测试套件以确保一切正常工作：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/symfony-aop-coroutine-bundle/tests

# 运行 PHPStan 分析
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/symfony-aop-coroutine-bundle
```

## 贡献

我们欢迎贡献！请遵循以下指南：

1. **提交 Issue** 报告错误、功能请求或问题
2. **创建拉取请求** 提交您的改进
3. **遵循 PSR 标准** 进行代码风格和格式化
4. **运行测试** 在提交 PR 前确保兼容性
5. **更新文档** 如果您的更改影响使用方式

### 开发环境设置

```bash
# 克隆仓库
git clone https://github.com/tourze/symfony-aop-coroutine-bundle.git

# 安装依赖
composer install

# 运行测试
./vendor/bin/phpunit packages/symfony-aop-coroutine-bundle/tests
```

## 许可证

本项目采用 MIT 许可证 - 详情请参阅 [LICENSE](LICENSE) 文件。

**作者**：tourze 团队

## 更新日志

有关版本更新和重要更改，请参阅 Git 提交历史。
