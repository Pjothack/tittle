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

        $wrap = function ($class) {
            return 'Tittle\\Application\\Controllers\\' . $class;
        };

        $controllers->get('status', $wrap('Status::index'));

        $controllers->get('locations', $wrap('Locations::getAll'));

        $controllers->get('traffic_levels', $wrap('TrafficLevels::get'));

        return $controllers;
    }
}
