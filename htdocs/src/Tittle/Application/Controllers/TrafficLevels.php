<?php

namespace Tittle\Application\Controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Tittle\DatabaseModels\TrafficLevel as TrafficLevelModel;
use Tittle\Environment;

class TrafficLevels
{
    public function get(Request $request, Application $app)
    {
        $traffic_levels = TrafficLevelModel::all();

        return $app->json($traffic_levels);
    }
}
