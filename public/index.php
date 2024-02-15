<?php

declare(strict_types=1);

use Pozys\PageAnalyzer\Database\Connection;
use Pozys\PageAnalyzer\Repositories\{UrlCheckRepository, UrlRepository};
use Pozys\PageAnalyzer\Services\{DiDomParser, GuzzleHttpService};
use Slim\Factory\AppFactory;
use DI\Container;
use Pozys\PageAnalyzer\Controllers\{HomeController, UrlCheckController, UrlController};
use Slim\Flash\Messages;
use Valitron\Validator;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(dirname(__DIR__) . '/templates');
});

$container->set('pdo', fn () => (new Connection())->get()->connect());

$container->set('validator', function () {
    $validator = new Validator();
    $validator->setPrependLabels(false);

    return $validator;
});

$container->set('urlRepository', fn () => new UrlRepository($container->get('pdo')));
$container->set('urlCheckRepository', fn () => new UrlCheckRepository($container->get('pdo')));

$container->set('http', fn () => new GuzzleHttpService());

$container->set('htmlParser', fn () => new DiDomParser());

$container->set('flash', function () {
    $storage = [];
    return new Messages($storage);
});

$app = AppFactory::createFromContainer($container);

$app->add(
    function ($request, $next) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->get('flash')->__construct($_SESSION);

        return $next->handle($request);
    }
);

$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$container->set('router', $router);

$app->get('/', HomeController::class)->setName('home');

$app->get('/urls/{id:[0-9]+}', [UrlController::class, 'show'])->setName('urls.show');

$app->get('/urls', [UrlController::class, 'index'])->setName('urls.index');

$app->post('/urls', [UrlController::class, 'store']);

$app->post('/urls/{url_id:[0-9]+}/checks', [UrlCheckController::class, 'checkUrl'])
    ->setName('urls.checks');

$app->run();
