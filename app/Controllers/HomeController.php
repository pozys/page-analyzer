<?php

declare(strict_types=1);

namespace Pozys\PageAnalyzer\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class HomeController
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $renderer = $this->container->get('renderer');
        $renderer->setLayout("layout.php");

        $flash = $this->container->get('flash')->getMessages();

        return $renderer->render($response, 'index.phtml', compact('flash'));
    }
}
