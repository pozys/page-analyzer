<?php

declare(strict_types=1);

namespace Pozys\PageAnalyzer\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class UrlCheckController
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function checkUrl(Request $request, Response $response, array $args): Response
    {
        $url = $this->container->get('urlRepository')->getUrlById((int) $args['url_id']);

        $httpResponse = $this->container->get('http')->checkUrl($url['name']);

        if ($httpResponse === null) {
            $this->container->get('flash')->addMessage('warning', 'Не удалось проверить сайт. Попробуйте позже');

            return $response->withRedirect(
                $this->container->get('router')->urlFor('urls.show', ['id' => $args['url_id']])
            );
        }

        $parsedPage = $this->container->get('htmlParser')->parseHtml($httpResponse['html']);

        $check = [];
        $check['url_id'] = $args['url_id'];
        $check['status_code'] = $httpResponse['status_code'];
        $check['h1'] = $parsedPage['h1'] ?? '';
        $check['title'] = $parsedPage['title'] ?? '';
        $check['description'] = $parsedPage['content'];

        try {
            $this->container->get('urlCheckRepository')->insertCheck($check);

            $this->container->get('flash')->addMessage('success', 'Страница успешно проверена');
        } catch (\PDOException $e) {
            $this->container->get('flash')->addMessage('error', $e->getMessage());
        }

        return $response->withRedirect($this->container->get('router')->urlFor('urls.show', ['id' => $args['url_id']]));
    }
}
