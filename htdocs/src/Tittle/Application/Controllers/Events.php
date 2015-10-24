<?php

namespace Tittle\Application\Controllers;

use GuzzleHttp\Client;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Tittle\Environment;

class Events
{
    public function index(Request $request, Application $app)
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

        return $app->json($locations_with_addresses);
    }
}
