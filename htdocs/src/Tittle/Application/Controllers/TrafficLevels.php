<?php

namespace Tittle\Application\Controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Tittle\DatabaseModels\TrafficLevel as TrafficLevelModel;
use Tittle\Environment;

class TrafficLevels
{
    public function getByLocation(Request $request, Application $app, $id)
    {
        $traffic_levels = TrafficLevelModel::where('location_id', $id)->get();

        return $app->json($traffic_levels);
    }
}
