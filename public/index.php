<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$templatePath = 'index.phtml';

$app->get('/', function (Request $request, Response $response) use ($templatePath) {
    $params = ['hello' => 'Hello!'];

    return $this->get('renderer')->render($response, $templatePath, $params);
});

$app->run();
