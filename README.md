# Proton

[![Latest Version](http://img.shields.io/packagist/v/alexbilbie/proton.svg?style=flat-square)](https://github.com/alexbilbie/proton/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)<br />
[![Build Status](https://img.shields.io/travis/alexbilbie/Proton/master.svg?style=flat-square)](https://travis-ci.org/alexbilbie/Proton)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/alexbilbie/proton.svg?style=flat-square)](https://scrutinizer-ci.com/g/alexbilbie/proton/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/alexbilbie/proton.svg?style=flat-square)](https://scrutinizer-ci.com/g/alexbilbie/proton)

Proton is a [StackPHP](http://stackphp.com/) compatible micro framework.

Under the hood it uses [Orno\Route](https://github.com/orno/route) for routing, [Orno\Di](https://github.com/orno/di) for dependency injection, and [League\Event](https://github.com/thephpleague/event) for event dispatching.

## Installation

Just add `"alexbilbie/proton": "1.0.*"` to your `composer.json` file.

## Setup

Basic usage with anonymous functions:

```php
// index.php
<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Proton\Application();

$app->get('/', function ($request, $response) {
    $response->setContent('<h1>It works!</h1>');
    return $response;
});

$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->setContent(
        sprintf('<h1>Hello, %s!</h1>', $args['name'])
    );
    return $response;
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

$app->get('/', 'HomeController::index'); // calls index method on HomeController class

$app->run();
```

```php
// HomeController.php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    public function index(Request $request, Response $response, array $args)
    {
        $response->setContent('<h1>It works!</h1>');
        return $response;
    }
}
```

Basic usage with StackPHP (using `Stack\Builder` and `Stack\Run`):

```php
// index.php
<?php
require __DIR__.'/../vendor/autoload.php';

$app = new Proton\Application();

$app->get('/', function ($request, $response) {
    $response->setContent('<h1>It works!</h1>');
    return $response;
});

$stack = (new Stack\Builder())
    ->push('Some/MiddleWare') // This will execute first
    ->push('Some/MiddleWare') // This will execute second
    ->push('Some/MiddleWare'); // This will execute third

$app = $stack->resolve($app);
Stack\run($app); // The app will run after all the middlewares have run
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
