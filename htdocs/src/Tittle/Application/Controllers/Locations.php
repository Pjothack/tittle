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
    public function getAll(Request $request, Application $app)
    {
        $locations = LocationModel::all();

        return $app->json($locations);
    }

    private function fetchRecent()
    {
        $client = new Client(['base_uri' => Environment::VISIT_TAMPERE_API_URL]);
        $res = $client->request('GET', 'search', [
            'query' => [
                'type' => 'location',
                'limit' => 50,
            ],
        ]);

        $locations = json_decode($res->getBody(), true);

        $locations_with_addresses = array_filter($locations, function ($loc) {
            return isset($loc['contact_info'], $loc['contact_info']['address']);
        });

        $api_key = $_ENV['GEOCODER_API_KEY'];

        $geocoder = new \GoogleMapsGeocoder();
        $geocoder->setApikey($api_key);
        $geocoder->setBounds(61.370922, 23.409324, 61.564102, 24.109702);

        array_walk($locations_with_addresses, function ($loc) use ($geocoder) {
            $possible_model = LocationModel::where('title', '=', $loc['title'])->first();

            $model = $possible_model ?: new LocationModel;

            if ($model->latitude == 0 || $model->longitude == 0) {
                $geocoder->setAddress($loc['contact_info']['address']);
                $response = $geocoder->geocode();

                if (isset($response['results'])) {
                    $location = $response['results'][0]['geometry']['location'];
                } else {
                    $location = [
                        'lat' => null,
                        'lng' => null,
                    ];
                }

                $model->latitude = $location['lat'];
                $model->longitude = $location['lng'];
            }

            $model->title = $loc['title'];
            $model->description = $loc['description'];
            $model->save();
        });
    }
}
