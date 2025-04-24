# AopCoroutineBundle

基于 Symfony 的协程支持包，通过 AOP 技术实现服务的协程化，使每个请求上下文拥有独立的服务实例。

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)
[![Build Status](https://img.shields.io/travis/tourze/symfony-aop-coroutine-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/symfony-aop-coroutine-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/symfony-aop-coroutine-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-aop-coroutine-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-aop-coroutine-bundle)

## 功能特性

- 基于 AOP 的协程服务隔离
- 支持通过注解 `#[AsCoroutine]` 标记服务为协程服务
- 默认支持常用服务（如 request_stack、twig、monolog 等）的协程化
- 每个请求自动分配独立上下文，自动清理资源
- 日志自动增强，支持协程 ID 追踪

## 安装说明

- 依赖 PHP 8.1 及以上
- 依赖 Symfony 6.4 及以上
- 通过 Composer 安装：

```bash
composer require tourze/symfony-aop-coroutine-bundle
```

- 在 `config/bundles.php` 注册 Bundle：

```php
return [
    Tourze\Symfony\AopCoroutineBundle\AopCoroutineBundle::class => ['all' => true],
];
```

## 快速开始

1. 使用 `#[AsCoroutine]` 注解你的服务：

```php
use Tourze\Symfony\AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class UserService
{
    private $state;
    public function setState($state) { $this->state = $state; }
}
```

2. 在控制器中注入并使用服务，每个请求拥有独立状态。

## 配置与高级特性

- 默认已为常用服务开启协程支持，也可通过服务标签/继承/注解自定义更多服务
- 日志处理器自动为日志添加 context_id
- 支持 Doctrine EntityManager 与 Session Handler 的协程化

## 注意事项

- 协程服务会占用更多内存，仅对需要隔离状态的服务使用
- 不支持返回 static 的方法，不建议用于单例服务
- 注意避免循环依赖

## 贡献指南

- 欢迎提交 Issue 与 PR
- 代码需符合 PSR 标准，建议先运行测试用例
- 测试命令：

```bash
./vendor/bin/phpunit packages/symfony-aop-coroutine-bundle/tests
```

## 版权和许可

- 开源协议：MIT
- 作者：tourze 团队

## 更新日志

详见 CHANGELOG 或 Git 提交历史
