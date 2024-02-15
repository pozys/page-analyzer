<?php

declare(strict_types=1);

namespace Pozys\PageAnalyzer\Controllers;

use Pozys\PageAnalyzer\Services\{UrlService, ValidationService};
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class UrlController
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $url = $this->container->get('urlRepository')->getUrlById((int) $args['id']);

        if ($url === null) {
            $this->container->get('flash')->addMessage('warning', "Url with id {$args['id']} not found");

            return $response->withRedirect($this->container->get('router')->urlFor('home'));
        }

        $renderer = $this->container->get('renderer');
        $renderer->setLayout("layout.php");

        $flash = $this->container->get('flash')->getMessages();

        $checkRepo = $this->container->get('urlCheckRepository');
        $checks = $checkRepo->checksByUrl($url['id']);

        $checkPath = $this->container->get('router')->urlFor('urls.checks', ['url_id' => $url['id']]);

        $params = compact('url', 'flash', 'checks', 'checkPath');

        return $renderer->render($response, 'urls/show.phtml', $params);
    }

    public function index(Request $request, Response $response): Response
    {
        $urls = $this->container->get('urlRepository')->listUrls();

        $renderer = $this->container->get('renderer');
        $renderer->setLayout("layout.php");

        return $renderer->render($response, 'urls/index.phtml', compact('urls'));
    }

    public function store(Request $request, Response $response): Response
    {
        $url = $request->getParsedBodyParam('url');

        $validator = new ValidationService($url);
        $errors = $validator->validateUrl();

        if (count($errors) > 0) {
            $validation = [
                'errors' => $errors,
                'old' => $url,
            ];

            $response = $response->withStatus(422);

            $params = compact('validation');

            $renderer = $this->container->get('renderer');
            $renderer->setLayout("layout.php");

            return $renderer->render($response, 'index.phtml', $params);
        }

        $urlParsed = parse_url($url['name']);

        $router = $this->container->get('router');

        if ($urlParsed === false) {
            $this->container->get('flash')->addMessage('error', 'Invalid URL');

            return $response->withRedirect($router->urlFor('home'));
        }

        $name = UrlService::getName($urlParsed);

        try {
            $id = $this->container->get('urlRepository')->firstByField('name', $name)['id'] ?? null;
        } catch (\PDOException $e) {
            $this->container->get('flash')->addMessage('error', $e->getMessage());

            return $response->withRedirect($router->urlFor('home'));
        }

        $message = 'Страница уже существует';

        if ($id === null) {
            try {
                $id = $this->container->get('urlRepository')->insertUrl($url);
            } catch (\Throwable $th) {
                $this->container->get('flash')->addMessage('error', $th->getMessage());

                return $response->withRedirect($router->urlFor('home'));
            }
            $message = 'Страница успешно добавлена';
        }

        $this->container->get('flash')->addMessage('success', $message);

        return $response->withRedirect($router->urlFor('urls.show', compact('id')));
    }
}
