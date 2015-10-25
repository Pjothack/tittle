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

        $controllers->get('locations', $wrap('Locations::get'));
        $controllers->post('locations', $wrap('Locations::add'));
        $controllers->put('locations/{id}', $wrap('Locations::update'))->assert('id', '\d+');
        $controllers->get('locations/{id}/discounts', $wrap('Discounts::getByLocation'))->assert('id', '\d+');
        $controllers->post('locations/{id}/discounts', $wrap('Discounts::addToLocation'))->assert('id', '\d+');
        $controllers->get('locations/{id}/traffic_levels', $wrap('TrafficLevels::getByLocation'))->assert('id', '\d+');
        $controllers->post('locations/{id}/traffic_levels', $wrap('TrafficLevels::addToLocation'))->assert('id', '\d+');

        return $controllers;
    }
}
