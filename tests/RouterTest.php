<?php

class RouterTest extends PHPUnit_Framework_TestCase
{


    public function testData()
    {
        $router = new \EasyRoute\Router();

        $router->get('/', function(){
            return 'controller index';
        })->name('index');

        $router->post('/product', function(){
            return 'Create Product';
        })->name('setProduct');

        $router->put('/items/{id}', function($id){
            return 'Update Item ' . $id;
        })->where([
            'id' => '[0-9]+'
        ])->name('updateItem');

        $data = $router->getData();

        $this->assertEquals('', $data['base_uri']);

    }

    /**
     * @expectedException \EasyRoute\Exception\BadRouteException
     */
    public function testBadRouteException()
    {
        $router = new \EasyRoute\Router([]);
    }



}