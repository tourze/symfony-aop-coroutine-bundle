# AopCoroutineBundle

基于 Symfony 的协程支持包，通过 AOP 技术实现服务的协程化，使得每个请求上下文都有独立的服务实例。

## 核心功能

### 协程服务管理

通过 `#[AsCoroutine]` 注解标记需要协程支持的服务：

```php
use AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class UserService
{
    private $state;
    
    public function setState($state)
    {
        $this->state = $state;
    }
}
```

### 内置协程服务

以下服务默认启用了协程支持：

- 请求相关服务
  - `request_stack`
  - `session_listener`
  - `router_listener`
- Twig 相关服务
  - `twig`
  - `twig.loader.native_filesystem`
- 安全相关服务
  - `security.untracked_token_storage`
  - `security.csrf.token_storage`
- 日志服务
  - `monolog.logger`
  - `monolog.logger.*`
- 其他服务
  - `debug.stopwatch`
  - Doctrine EntityManager
  - Session Handler

### 协程上下文管理

- 自动为每个请求创建独立的协程上下文
- 在请求结束时自动清理协程资源
- 支持协程 ID 的日志追踪

### 日志增强

自动为日志添加协程相关信息：

```php
// 日志中会自动包含 coroutine_id
$logger->info('Some message');
```

## 配置说明

1. 在 `config/bundles.php` 中注册 Bundle：

```php
return [
    AopCoroutineBundle\AopCoroutineBundle::class => ['all' => true],
];
```

2. 标记需要协程支持的服务：

```php
use AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class MyService
{
    // 服务实现...
}
```

## 使用示例

### 1. 创建协程服务

```php
use AopCoroutineBundle\Attribute\AsCoroutine;

#[AsCoroutine]
class StateService
{
    private array $state = [];
    
    public function setState(string $key, $value): void
    {
        $this->state[$key] = $value;
    }
    
    public function getState(string $key)
    {
        return $this->state[$key] ?? null;
    }
}
```

### 2. 在控制器中使用

```php
class UserController
{
    public function __construct(
        private StateService $stateService
    ) {}
    
    public function action1()
    {
        // 每个请求都有独立的状态
        $this->stateService->setState('key', 'value1');
    }
    
    public function action2()
    {
        // 不会受到其他请求的影响
        $value = $this->stateService->getState('key');
    }
}
```

## 注意事项

1. 性能考虑
   - 协程服务会占用额外的内存
   - 建议只对真正需要状态隔离的服务使用协程
   - 在请求结束时会自动清理协程资源

2. 使用限制
   - 不支持返回 static 的方法
   - 不支持单例模式的服务
   - 需要注意循环依赖的问题

3. 调试建议
   - 使用日志中的 coroutine_id 追踪请求
   - 注意观察内存使用情况
   - 在开发环境可以开启调试日志

4. 最佳实践
   - 优先使用无状态服务
   - 将状态集中在少数几个服务中
   - 及时清理不需要的状态
