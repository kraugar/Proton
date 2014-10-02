# Proton

Proton is a [StackPHP](http://stackphp.com/) compatible micro framework.

Under the hood it uses [Orno\Route](https://github.com/orno/route) for routing, [Orno\Di](https://github.com/orno/di) for dependency injection, and [League\Event](https://github.com/thephpleague/event) for event dispatching.

## Installation

Just add `"alexbilbie/proton": "0.1.*"` to your `composer.json` file.

## Setup

Basic usage with anonymous functions:

```php
// index.php
<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Proton\Application();

$app->get('/', function ($request, $response) {
    $response->setContent('<h1>It works!</h1>');
});

$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->setContent(
        sprintf('<h1>Hello, %s!</h1>', $args['name'])
    );
});

$app->run();
```

Basic usage with controllers:

```php
// index.php
<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Proton\Application();

$app['HomeController'] = function () {
    return new HomeController();
});

$app->get('/', 'HomeController@index');

$app->run();
```

```php
// HomeController.php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    public function index(Request $request, Response $response, $args)
    {
        $response->setContent('<h1>It works!</h1>');
    }
}
```

Basic usage with StackPHP (using `Stack\Builder` and `Stack\Run`):

```php
// index.php
<?php
require __DIR__.'/../vendor/autoload.php';

$app = new Snapcam\Api\Application();

$app->get('/', function ($request, $response) {
    $response->setContent('<h1>It works!</h1>');
});

$stack = (new Stack\Builder())
    ->push('Some/MiddleWare');
    ->push('Some/MiddleWare')
    ->push('Some/MiddleWare');

$app = $stack->resolve($app);
Stack\run($app);
```

## Events

You can intercept requests and responses at three points during the lifecycle:

### request.received

```php
$app->subscribe('request.received', function ($event) {
    // access the request using $event->getRequest()
})
```

This event is fired when a request is received but before it has been processed by the router.

### response.before

```php
$app->subscribe('response.before', function ($event) {
    // access the request using $event->getRequest()
    // access the response using $event->getResponse()
})
```

This event is fired when a response has been created but before it has been output.

### response.after

```php
$app->subscribe('response.after', function ($event) {
    // access the request using $event->getRequest()
    // access the response using $event->getResponse()
})
```

This event is fired when a response has been output and before the application lifecycle is completed.

## Dependency Injection Container

Proton uses `Orno/Di` as its dependency injection container.

You can bind singleton objects into the container from the main application object using ArrayAccess:

```php
$app['db'] = function () {
    $manager = new Illuminate\Database\Capsule\Manager;

    $manager->addConnection([
        'driver'    => 'mysql',
        'host'      => $config['db_host'],
        'database'  => $config['db_name'],
        'username'  => $config['db_user'],
        'password'  => $config['db_pass'],
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ], 'default');

    $manager->setAsGlobal();

    return $manager;
};
```

or by accessing the container directly:

```php
$app->getContainer()->singleton('db', function () {
    $manager = new Illuminate\Database\Capsule\Manager;

    $manager->addConnection([
        'driver'    => 'mysql',
        'host'      => $config['db_host'],
        'database'  => $config['db_name'],
        'username'  => $config['db_user'],
        'password'  => $config['db_pass'],
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ], 'default');

    $manager->setAsGlobal();

    return $manager;
});
```

multitons can be added using the `add` method on the container:

```php
$app->getContainer()->add('foo', function () {
        return new Foo();
});
```

For easy testing down the road it is recommended you embrace constructor injection:

```php
$app->getContainer()->add('Bar', function () {
        return new Bar();
});

$app->getContainer()->add('Foo', function () use ($app) {
        return new Foo(
            $app->getContainer()->get('Bar')
        );
});
```
