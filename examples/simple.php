<?php
error_reporting(E_ALL);
//php examples/simple.php
//phpunit --bootstrap vendor/bootstrap.php tests

$time = microtime(true);

include __DIR__ . '/../vendor/autoload.php';

use EasyRoute\Router;

$cache_file = __DIR__ . '/route.cache.dat';

//if (!file_exists($cache_file)) {


    $router = new Router();

    $router->get('/examples/{php:[simple|simple2][a-z]+}.php', function ($php) {
        return "controller index. File => $php";
    })->setName('index')->setHost('routes2.dev')->setScheme('http');

    $router->get('/examples/simple.php', function () {
        return "controller index";
    })->setName('basic2');

    $router->get('/examples/rwerew/simple.php', function () {
        return "controller index";
    })->setName('basic');

    $router->get('/examples/{id:[0-9]+}/simple.php', function () {
        return "controller index";
    })->setName('basic_params');

    $router->group(['scheme' => 'http', 'prefix' => 'examples2'], function (Router $router) {

        $router->get('/simple.php', function () {
            return 'controller examples index';
        })->setName('basic_scheme_url');

    });

    $router->group(['scheme' => 'http', 'host' => 'demo3.dev', 'prefix' => 'examples2'], function (Router $router) {

        $router->get('/simple.php', function () {
            return 'controller examples index';
        })->setName('basic_scheme_host_url');

    });

    $router->group(['scheme' => 'http', 'host' => 'demo3.dev', 'prefix' => 'examples2'], function (Router $router) {

        $router->get('/{url:[a-z]+}/{id:[0-9]+}/simple.php', function () {
            return 'controller examples index';
        })->setName('basic_scheme_host_url_params');

    });

    $router->group(['host' => 'routes.dev', 'prefix' => 'examples2'], function (Router $router) {

        $router->get('/simple.php', function () {
            return 'controller examples index';
        })->setName('examples');

    });

    $router->group(['scheme' => 'http', 'host' => '{prefix}.routes.dev', 'prefix' => 'examples'],
        function (Router $router) {

            $router->get('/simple.php', function ($prefix) {

                return "controller with scheme and host examples index. automatic Prefix = $prefix";

            })->setName('subdomain');

            $router->get('/viewitem_prefix_automatic', function () {
                return "view item";
            })->setName('veritem');

            for ($i = 0; $i < 1000; $i++) {
                $router->get('/no_prefix_veritem' . $i, function () {
                    return "ver item";
                })->setName('veritem' . $i);
            }

        });

    $router->group(['scheme' => 'http', 'host' => 'en.routes.dev', 'prefix' => 'examples'], function (Router $router) {

        $router->get('/simple.php', function () {

            return "controller with scheme and host examples index. Prefix = en";

        })->setName('subdomain');

        $router->get('/viewitem', function () {
            return "view item";
        })->setName('veritem');

    });

    $router->group(['scheme' => 'http', 'host' => 'es.routes.dev', 'prefix' => 'examples'], function (Router $router) {

        $router->get('/simple.php', function () {

            return "controller with scheme and host examples index. Prefix = es";

        })->setName('subdomain');

        $router->get('/prefix_es_veritem', function () {
            return "ver item";
        })->setName('veritem');

    });

    $router->get('/no_prefix_veritem', function () {
        return "ver item";
    })->setName('veritem');


    //$router->group(['scheme' => 'http', 'host' => 'routes.dev', 'prefix' => 'examples'], function(Router $router){
    //
    //    $router->get('/simple.php', function(){
    //        return 'controller examples index';
    //    })->name('examples');
    //
    //});
    //
    //$router->group(['scheme' => 'http', 'host' => '{prefix}.routes.dev', 'prefix' => 'examples'], function(Router $router){
    //
    //    $router->get('/simple.php', function($prefix){
    //        return "controller subdomain $prefix examples index";
    //    })->name('examples');
    //
    //});
    //
    //$router->post('/product', function(){
    //    return 'Create Product';
    //})->name('setProduct');
    //
    //$router->put('/items/{id}', function($id){
    //    return 'Amend Item ' . $id;
    //})->where([
    //    'id' => '[0-9]+'
    //])->name('amendItem');
    //
    //$router->group(['host' => 'local2.dev'], function(\EasyRoute\Router $router){
    //    $router->get('/', function () {
    //        return 'controller index local2.dev';
    //    })->name('index');
    //});


    $data = $router->getData();
//    file_put_contents($cache_file, json_encode($data));
//} else {
//    $data = json_decode(file_get_contents($cache_file), true);
//}
echo "\n";
echo microtime(true) - $time;
echo "\n";
echo '------------------';


$time = microtime(true);
$dispatcher = new \EasyRoute\Dispatcher($data);
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
echo "\n";
echo '------------------';
echo "\n";
echo $dispatcher->dispatchRequest($request->getMethod(), $request->getUri());
echo "\n";
echo '------------------';
echo $dispatcher->getUrlRequest('veritem', [], $request->getUri());
echo "\n";
echo '------------------';
echo "\n";
echo '------------------';
//echo $dispatcher->getUrlRequest('veritem9', ['prefix' => 'it'], $request->getUri());
echo "\n";
echo '------------------';
echo "\n";
echo microtime(true) - $time;
echo "\n";
echo '------------------';
die;


echo $dispatcher->getUrlRequest('basic', [], $request->getUri());
echo "\n";
echo $dispatcher->getUrlRequest('basic_params', ['id' => 321], $request->getUri());
echo "\n";
echo $dispatcher->getUrlRequest('basic2', [], $request->getUri());
echo "\n";
echo $dispatcher->getUrlRequest('basic_scheme_url', [], $request->getUri());
echo "\n";
echo $dispatcher->getUrlRequest('basic_scheme_host_url', [], $request->getUri());
echo "\n";
echo $dispatcher->getUrlRequest('basic_scheme_host_url_params', ['url' => 'url', 'id' => 23], $request->getUri());
echo "\n";
echo $dispatcher->getUrlRequest('veritem', [], $request->getUri());
echo "\n";


die;

//$request::enableHttpMethodParameterOverride();
//$request2 = new Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory();
//$psrrequest = $request2->createRequest($request);

//$psrrequest = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
//    $_SERVER,
//    $_GET,
//    $_POST,
//    $_COOKIE,
//    $_FILES
//);

//$response = new \Symfony\Component\HttpFoundation\RedirectResponse('http://google.com');
//return $response->send();

//var_dump($request->getBasePath());
//var_dump($psrrequest);
echo '<pre>';

var_dump(parse_url($request->getUri()));
var_dump(parse_url('/examples/demo'));
var_dump(parse_url('/examples/demo/?fdsfsd=fds'));
var_dump(parse_url('//www.ff.com/examples/demo/?fdsfsd=fds'));
var_dump(parse_url('//www.ff.com.br/examples/demo/?fdsfsd=fds'));
var_dump(parse_url('//www.dssd.dss.ff.com.br/examples/demo/?fdsfsd=fds'));

echo "----------------------------------------\n";

var_dump($request->getMethod());
var_dump($request->getScheme());
var_dump($request->getBasePath());
var_dump($request->getUri());
var_dump($request->getBaseUrl());
var_dump($request->getClientIp());
var_dump($request->getHost());
var_dump($request->getDefaultLocale());
var_dump($request->getHttpHost());
var_dump($request->getHttpMethodParameterOverride());
var_dump($request->getLanguages());
var_dump($request->getPathInfo());
var_dump($request->getQueryString());
var_dump($request->getRequestUri());
var_dump($request->getSchemeAndHttpHost());
var_dump($request->getUriForPath('examples'));
// POST //
//var_dump($request->request->all());
var_dump($request->getBasePath());
echo "----------------------------------------\n";
var_dump($psrrequest->getMethod());
var_dump($psrrequest->getAttributes());
var_dump($psrrequest->getRequestTarget());
var_dump($psrrequest->getQueryParams());
var_dump($psrrequest->getServerParams());
var_dump($psrrequest->getCookieParams());
var_dump($psrrequest->getUri()->__toString());

echo "post\n";
echo "----\n";
var_dump($psrrequest->getParsedBody());
echo "----\n";

var_dump($psrrequest->getUri()->getPath());
var_dump($psrrequest->getUri()->getQuery());
var_dump($psrrequest->getUri()->getFragment());

var_dump($psrrequest->getUri()->getAuthority());
var_dump($psrrequest->getUri()->getHost());
var_dump($psrrequest->getUri()->getPort());
var_dump($psrrequest->getUri()->getScheme());
var_dump($psrrequest->getUri()->getUserInfo());
echo '</pre>';
?>

    <form action="#" method="post">
        <input type="hidden" name="demo" value="valuepost">
        <input type="hidden" name="post_name" value="post_value">
        <button type="submit">Enviar</button>
        <input type="hidden" name="_method" value="PUT">
    </form>

<?php
die;

$dispatcher->dispatch($psrrequest);