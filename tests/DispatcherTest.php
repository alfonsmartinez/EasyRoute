<?php

class DispatcherTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    public static $data;

    /**
     * @var \EasyRoute\Router
     */
    public static $router;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public static function setUpBeforeClass()
    {
        $router = new \EasyRoute\Router();

        $router->get('/', function () {
            return 'controller index';
        })->name('index');

        $router->post('/product', function () {
            return 'Create Product';
        })->name('setProduct');

        $router->put('/items/{id}', function ($id) {
            return 'Update Item ' . $id;
        })->where([
            'id' => '[0-9]+'
        ])->name('updateItem');

        $router->group(['prefix' => 'admin'], function(\EasyRoute\Router $router){
            $router->get('/', function () {
                return 'controller admin/index local.dev';
            })->name('index');
        });

        $router->group(['domain' => 'local2.dev'], function(\EasyRoute\Router $router){
            $router->get('/', function () {
                return 'controller index local2.dev';
            })->name('index');
        });

        $router->group(['domain' => 'local3.dev'], function(\EasyRoute\Router $router){
            $router->get('/local3', function () {
                return 'controller index local2.dev';
            })->name('index');
        });

        self::$router = $router;
    }


    public function testResponses()
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());

        $response = $dispatcher->dispatchRequest('GET', '/');
        $this->assertEquals('controller index', $response);

        $response = $dispatcher->dispatchRequest('GET', '/', 'local.dev');
        $this->assertEquals('controller index', $response);

        $response = $dispatcher->dispatchRequest('GET', '/admin/');
        $this->assertEquals('controller admin/index local.dev', $response);

        $response = $dispatcher->dispatchRequest('GET', '/admin');
        $this->assertEquals('controller admin/index local.dev', $response);

        $response = $dispatcher->dispatchRequest('GET', '/admin', 'local.dev');
        $this->assertEquals('controller admin/index local.dev', $response);

        $response = $dispatcher->dispatchRequest('GET', '/admin', 'local2.dev');
        $this->assertEquals('controller admin/index local.dev', $response);

        $response = $dispatcher->dispatchRequest('GET', '/admin', 'local3.dev');
        $this->assertEquals('controller admin/index local.dev', $response);

        $response = $dispatcher->dispatchRequest('GET', '/', 'local2.dev');
        $this->assertEquals('controller index local2.dev', $response);

        $response = $dispatcher->dispatchRequest('POST', '/product');
        $this->assertEquals('Create Product', $response);

        $response = $dispatcher->dispatchRequest('POST', '/product', 'local.dev');
        $this->assertEquals('Create Product', $response);

        $response = $dispatcher->dispatchRequest('PUT', '/items/11');
        $this->assertEquals('Update Item 11', $response);

        $response = $dispatcher->dispatchRequest('PUT', '/items/11', 'local.dev');
        $this->assertEquals('Update Item 11', $response);


    }

    /**
     * @expectedException \EasyRoute\Exception\HttpRouteNotFoundException
     */
    public function testExceptionHttpRouteNotFound()
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());
        $dispatcher->dispatchRequest('POST', '/items/11');
    }

    /**
     * @expectedException \EasyRoute\Exception\HttpRouteNotFoundException
     */
    public function test2ExceptionHttpRouteNotFound()
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());
        $dispatcher->dispatchRequest('GET', '/demo');
    }

    /**
     * @expectedException \EasyRoute\Exception\HttpMethodNotAllowedException
     */
    public function testHttpMethodNotAllowedException()
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());
        $dispatcher->dispatchRequest('NOT', '/demo');
    }

    public function testUrls()
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());

        $url = $dispatcher->getUrlRequest('setProduct', [], '/');
        $this->assertEquals('/product', $url);
    }

    public function test2Urls()
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());

        $url = $dispatcher->getUrlRequest('setProduct', [], '/', 'local.dev');
        $this->assertEquals('/product', $url);
    }

    /**
     * @dataProvider getUrls
     */
    public function test2DynamicUrls($name, $params, $requesturi, $domain, $result)
    {
        $dispatcher = new \EasyRoute\Dispatcher(self::$router->getData());

        $url = $dispatcher->getUrlRequest($name, $params, $requesturi, $domain);
        $this->assertEquals($result, $url);
    }

    public function getUrls()
    {
        return [
            ['index', [], '/', 'local.dev', ''],
            ['index', [], '/admin', 'local.dev', '/admin'],
            ['index', [], '/', 'local2.dev', ''],
            ['index', [], '/', 'local3.dev', '/local3'],
            ['setProduct', [], '/', 'local.dev', '/product']
        ];
    }
}