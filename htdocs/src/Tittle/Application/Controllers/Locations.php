<?php

namespace Tittle\Application\Controllers;

use GuzzleHttp\Client;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Tittle\DatabaseModels\Location as LocationModel;
use Tittle\Environment;

class Locations
{
    public function index(Request $request, Application $app)
    {
        $this->fetchRecent();

        return new Response('ok');
    }

    private function fetchRecent()
    {
        $client = new Client(['base_uri' => Environment::VISIT_TAMPERE_API_URL]);
        $res = $client->request('GET', 'search', [
            'query' => [
                'type' => 'location',
                'limit' => 100,
            ],
        ]);

        $locations = json_decode($res->getBody(), true);

        $locations_with_addresses = array_filter($locations, function ($loc) {
            return isset($loc['contact_info'], $loc['contact_info']['address']);
        });

        array_walk($locations_with_addresses, function ($loc) {
            $possible_model = LocationModel::where('title', '=', $loc['title'])->first();

            $model = $possible_model ?: new LocationModel;

            $model->title = $loc['title'];
            $model->description = $loc['description'];
            $model->save();
        });
    }
}
