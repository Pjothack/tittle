<?php

namespace Tittle\Application\Controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Tittle\DatabaseModels\TrafficLevel as TrafficLevelModel;
use Tittle\Environment;

class TrafficLevels
{
    public function addToLocation(Request $request, Application $app, $id)
    {
        $level = new TrafficLevelModel;
        $level->location_id = $id;
        $level->level = $request->get('level');

        $level->save();

        return new Response('ok');
    }

    public function getByLocation(Request $request, Application $app, $id)
    {
        $traffic_levels_builder = TrafficLevelModel
            ::where('location_id', $id)
            ->limit(60);

        $average = $traffic_levels_builder->avg('level');
        $count = $traffic_levels_builder->count('level');

        return $app->json([
            'average' => $average,
            'count' => $count,
        ]);
    }
}
