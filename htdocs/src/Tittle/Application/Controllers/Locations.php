<?php

namespace Tittle\Application\Controllers;

use GuzzleHttp\Client;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Tittle\DatabaseModels\Category as CategoryModel;
use Tittle\DatabaseModels\Location as LocationModel;
use Tittle\Environment;

class Locations
{
    public function get(Request $request, Application $app)
    {
        $location_builder = LocationModel::leftJoin('categories', 'locations.category_id', '=', 'categories.id');

        if ($request->get('category')) {
            $location_builder = $location_builder->where('categories.name', $request->get('category'));
        }

        if ($request->get('with_description')) {
            $location_builder = $location_builder->where('categories.name', $request->get('category'));
        }

        if ($request->get('limit')) {
            $location_builder = $location_builder->limit($request->get('limit'));
        }

        $locations = $location_builder->get()->toArray();

        $stripped_locations = array_map(function ($loc) use ($request) {
            $returnable = [
                'id' => $loc['id'],
                'title' => $loc['title'],
                'latitude' => $loc['latitude'],
                'longitude' => $loc['longitude'],
                'category' => $loc['name'],
            ];

            if ($request->get('with_description')) {
                $returnable['description'] = $loc['description'];
            }

            return $returnable;
        }, $locations);

        return $app->json($stripped_locations);
    }

    public function fetchRecent($offset)
    {
        $client = new Client(['base_uri' => Environment::VISIT_TAMPERE_API_URL]);
        $res = $client->request('GET', 'search', [
            'query' => [
                'type' => 'location',
                'limit' => 100,
                'offset' => $offset,
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

            $category = CategoryModel::byTags($loc['tags']);
            if ($category) {
                $model->category_id = $category->id;
            } else {
                error_log('could not match tag to location: '. print_r($loc['tags'], true));
            }

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
