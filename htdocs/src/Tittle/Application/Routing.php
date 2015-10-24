<?php
/**
 * Backend routes
 *
 * @author Oliver Vartiainen <oliver@24rent.fi>
 */

namespace Tittle\Application;

use Silex\ControllerProviderInterface;
use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Routing implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        \Tittle\Environment::initializeEloquent();

        $controllers = $app['controllers_factory'];

        $controllers->get('/', function () {
            return new Response('PONG');
        });

        $controllers->get('status', 'Tittle\\Application\\Controllers\\Status::status');

        return $controllers;
    }
}
