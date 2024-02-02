<?php

use App\Database\Connection;
use App\Database\PostgresDML;
use App\Models\Url;
use App\Repositories\UrlRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Valitron\Validator;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('pdo', fn () => (new Connection())->get()->connect());

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) {
    $params = ['hello' => 'Hello!'];
    $renderer = $this->get('renderer');
    $renderer->setLayout("layout.php");

    return $renderer->render($response, 'index.phtml', $params);
});

$app->get('/urls', function (Request $request, Response $response) {
    $repo = new UrlRepository($this->get('pdo'));
    $urls = $repo->list();

    $renderer = $this->get('renderer');
    $renderer->setLayout("layout.php");

    $params = compact('urls');

    return $renderer->render($response, 'urls/index.phtml', $params);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    try {
        $urlData = $request->getParsedBodyParam('url');
        $urlData['name'] = parse_url($urlData['name'], PHP_URL_HOST);
        $validator = new Validator($urlData);
        $validator->rules(Url::rules());

        if ($validator->validate()) {
            echo "Yay! We're all good!";
        } else {
            dump($validator->errors());
            die;
        }

        $repo = new UrlRepository($this->get('pdo'));
        $id = $repo->insertUrl($urlData);
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }

    return $response->withRedirect($router->urlFor('urls.index'));
});

$app->run();
