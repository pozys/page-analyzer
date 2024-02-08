<?php

use App\Database\Connection;
use App\Models\Url;
use App\Repositories\{UrlCheckRepository, UrlRepository};
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Flash\Messages;
use Valitron\Validator;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('pdo', fn () => (new Connection())->get()->connect());

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

$app->get('/', function (Request $request, Response $response) {
    $renderer = $this->get('renderer');
    $renderer->setLayout("layout.php");

    $flash = $this->get('flash')->getMessages();

    $params = compact('flash');

    return $renderer->render($response, 'index.phtml', $params);
})->setName('home');

$app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($router) {
    $urlRepo = new UrlRepository($this->get('pdo'));

    $url = $urlRepo->getUrlById($args['id']);

    if ($url === null) {
        $this->get('flash')->addMessage('warning', "Url with id {$args['id']} not found");

        return $response->withRedirect($router->urlFor('home'));
    }

    $renderer = $this->get('renderer');
    $renderer->setLayout("layout.php");

    $flash = $this->get('flash')->getMessages();

    $checkRepo = new UrlCheckRepository($this->get('pdo'));
    $checks = $checkRepo->checksByUrl($url['id']);

    $checkPath = $router->urlFor('urls.checks', ['url_id' => $url['id']]);

    $params = compact('url', 'flash', 'checks', 'checkPath');

    return $renderer->render($response, 'urls/show.phtml', $params);
})->setName('urls.show');

$app->get('/urls', function (Request $request, Response $response) {
    $repo = new UrlRepository($this->get('pdo'));
    $urls = $repo->listUrls();

    $renderer = $this->get('renderer');
    $renderer->setLayout("layout.php");

    $params = compact('urls');

    return $renderer->render($response, 'urls/index.phtml', $params);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    $url = $request->getParsedBodyParam('url');

    $validator = new Validator($url);
    $validator->rules(Url::rules());

    if (!$validator->validate()) {
        $this->get('flash')->addMessage('validation', $validator->errors());

        return $response->withRedirect($router->urlFor('home'));
    }

    try {
        $repo = new UrlRepository($this->get('pdo'));

        $name = Url::getName(parse_url($url['name']));
        $id = $repo->firstByField('name', $name)['id'] ?? null;
    } catch (\PDOException $e) {
        $this->get('flash')->addMessage('error', $e->getMessage());

        return $response->withRedirect($router->urlFor('home'));
    }

    $message = 'Страница уже существует';

    if ($id === null) {
        $id = $repo->insertUrl($url);
        $message = 'Страница успешно добавлена';
    }

    $this->get('flash')->addMessage('success', $message);

    return $response->withRedirect($router->urlFor('urls.show', compact('id')));
});

$app->post('/urls/{url_id:[0-9]+}/checks', function (Request $request, Response $response, array $args) use ($router) {
    try {
        $repo = new UrlCheckRepository($this->get('pdo'));

        $repo->insertCheck($args['url_id']);

        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (\PDOException $e) {
        $this->get('flash')->addMessage('error', $e->getMessage());
    }

    return $response->withRedirect($router->urlFor('urls.show', ['id' => $args['url_id']]));
})->setName('urls.checks');

$app->run();
