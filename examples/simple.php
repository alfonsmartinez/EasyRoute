<?php

//php examples/simple.php
//phpunit --bootstrap vendor/bootstrap.php tests

include __DIR__ . '/../vendor/autoload.php';

use EasyRoute\Router;

$router = new Router();
$router->get('/', function(){
    return 'controller index';
})->name('index');

$router->post('/product', function(){
    return 'Create Product';
})->name('setProduct');

$router->put('/items/{id}', function($id){
    return 'Amend Item ' . $id;
})->where([
    'id' => '[0-9]+'
])->name('amendItem');

$router->group(['domain' => 'local2.dev'], function(\EasyRoute\Router $router){
    $router->get('/', function () {
        return 'controller index local2.dev';
    })->name('index');
});