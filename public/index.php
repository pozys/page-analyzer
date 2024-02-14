<?php

declare(strict_types=1);

use Pozys\PageAnalyzer\Database\Connection;
use Pozys\PageAnalyzer\Models\Url;
use Pozys\PageAnalyzer\Repositories\{UrlCheckRepository, UrlRepository};
use Pozys\PageAnalyzer\Services\{DiDomParser, GuzzleHttpService};
use Slim\Factory\AppFactory;
use DI\Container;
use Pozys\PageAnalyzer\Controllers\HomeController;
use Slim\Flash\Messages;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Valitron\Validator;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
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

$app->get('/', HomeController::class)->setName('home');

$app->get('/urls/{id:[0-9]+}', function (
    Request $request,
    Response $response,
    array $args
) use ($router) {
    $url = $this->get('urlRepository')->getUrlById((int) $args['id']);

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
    $urls = $this->get('urlRepository')->listUrls();

    $renderer = $this->get('renderer');
    $renderer->setLayout("layout.php");

    $params = compact('urls');

    return $renderer->render($response, 'urls/index.phtml', $params);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    $url = $request->getParsedBodyParam('url');

    $validator = Url::setRules($this->get('validator'));
    $validator = $validator->withData($url);

    if (!$validator->validate()) {
        $validation = [
            'errors' => $validator->errors(),
            'old' => $url,
        ];

        $response = $response->withStatus(422);

        $params = compact('validation');

        $renderer = $this->get('renderer');
        $renderer->setLayout("layout.php");

        return $renderer->render($response, 'index.phtml', $params);
    }

    $urlParsed = parse_url($url['name']);

    if ($urlParsed === false) {
        $this->get('flash')->addMessage('error', 'Invalid URL');

        return $response->withRedirect($router->urlFor('home'));
    }

    $name = Url::getName($urlParsed);

    try {
        $id = $this->get('urlRepository')->firstByField('name', $name)['id'] ?? null;
    } catch (\PDOException $e) {
        $this->get('flash')->addMessage('error', $e->getMessage());

        return $response->withRedirect($router->urlFor('home'));
    }

    $message = 'Страница уже существует';

    if ($id === null) {
        try {
            $id = $this->get('urlRepository')->insertUrl($url);
        } catch (\Throwable $th) {
            $this->get('flash')->addMessage('error', $th->getMessage());

            return $response->withRedirect($router->urlFor('home'));
        }
        $message = 'Страница успешно добавлена';
    }

    $this->get('flash')->addMessage('success', $message);

    return $response->withRedirect($router->urlFor('urls.show', compact('id')));
});

$app->post('/urls/{url_id:[0-9]+}/checks', function (
    Request $request,
    Response $response,
    array $args
) use ($router) {
    $url = $this->get('urlRepository')->getUrlById((int) $args['url_id']);

    $httpResponse = $this->get('http')->checkUrl($url['name']);

    if ($httpResponse === null) {
        $this->get('flash')->addMessage('warning', 'Не удалось проверить сайт. Попробуйте позже');

        return $response->withRedirect($router->urlFor('urls.show', ['id' => $args['url_id']]));
    }

    $parsedPage = $this->get('htmlParser')->parseHtml($httpResponse['html']);

    $check = [];
    $check['url_id'] = $args['url_id'];
    $check['status_code'] = $httpResponse['status_code'];
    $check['h1'] = $parsedPage['h1'] ?? '';
    $check['title'] = $parsedPage['title'] ?? '';
    $check['description'] = $parsedPage['content'];

    try {
        $this->get('urlCheckRepository')->insertCheck($check);

        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (\PDOException $e) {
        $this->get('flash')->addMessage('error', $e->getMessage());
    }

    return $response->withRedirect($router->urlFor('urls.show', ['id' => $args['url_id']]));
})->setName('urls.checks');

$app->run();
