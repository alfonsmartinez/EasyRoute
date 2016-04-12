<?php
error_reporting(E_ALL);
//php examples/simple.php
//phpunit --bootstrap vendor/bootstrap.php tests

include __DIR__ . '/../vendor/autoload.php';

use EasyRoute\Router;

$router = new Router('/examples');

$router->filter('auth', function ($_requesturi) {
    //return 'hola auth';
    var_dump($_requesturi);
});

$router->get('/', function () {
    return 'hello world';
});

$router->get('/part/', function () {
    return 'hello world part';
})->setName('part');

$router->get('/part/{id:[0-9]+}/', function ($id) {
    return 'hello world part' . $id;
})->setName('partid');

$router->group(['before' => ['auth']], function (\EasyRoute\Router $router) {
    $router->get('/testbefore/', function () {
        return 'hello test before';
    });
});

$router->group(['prefix' => 'en'], function (\EasyRoute\Router $router) {

    $router->get('/', function () {
        return 'home with prefix en';
    })->setName('home');

});

$router->group(['prefix' => 'es'], function (\EasyRoute\Router $router) {

    $router->get('/', function () {
        return 'home with prefix es';
    })->setName('home');

    $router->group(['prefix' => 'admin'], function (Router $router) {
        $router->get('/', function () {
            return 'hola 2 prefix';
        })->setName('prefix2');
    });

});

$router->get('/home/', function () {
    return 'home without prefix';
})->setName('home');

$data = $router->getData();
$dispatcher = new \EasyRoute\Dispatcher($data);

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

echo $dispatcher->getUrlRequest('home', [], 'http://routes.dev/examples/es/twtw/');
echo "\n";
echo $dispatcher->getUrlRequest('prefix2', [], 'http://routes.dev/examples/es/twtw/');
echo "\n";
echo $dispatcher->getUrlRequest('partid', ['id' => 555], 'http://routes.dev/examples/es/twtw/');
echo "\n";

try {
    echo $dispatcher->dispatchRequest($request->getMethod(), $request->getUri());
} catch (\EasyRoute\Exception\HttpRouteNotFoundException $e) {
    echo 'route not found';
} catch (\EasyRoute\Exception\HttpMethodNotAllowedException $e) {
    echo 'not method allowed';
} catch (\Exception $e) {
    echo 'error 500';
}