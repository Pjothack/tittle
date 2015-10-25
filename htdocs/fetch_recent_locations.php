<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

use Tittle\Environment;
use Tittle\DatabaseModels\Category as CategoryModel;
use Tittle\DatabaseModels\Location as LocationModel;

define('AREA_BOUND_SW_LATITUDE', 61.370922);
define('AREA_BOUND_SW_LONGITUDE', 23.409324);
define('AREA_BOUND_NE_LATITUDE', 61.564102);
define('AREA_BOUND_NE_LONGITUDE', 24.109702);

Environment::initializeEloquent();

$client = new Client(['base_uri' => Environment::VISIT_TAMPERE_API_URL]);
$res = $client->request('GET', 'search', [
    'query' => [
        'type' => 'location',
        'limit' => 100,
        'offset' => $_GET['offset'],
    ],
]);

$locations = json_decode($res->getBody(), true);

$locations_with_addresses = array_filter($locations, function ($loc) {
    return isset($loc['contact_info'], $loc['contact_info']['address']);
});

$api_key = $_ENV['GEOCODER_API_KEY'];

$geocoder = new \GoogleMapsGeocoder();
$geocoder->setApikey($api_key);
$geocoder->setBounds(
    AREA_BOUND_SW_LATITUDE,
    AREA_BOUND_SW_LONGITUDE,
    AREA_BOUND_NE_LATITUDE,
    AREA_BOUND_NE_LONGITUDE
);

foreach ($locations_with_addresses as $loc) {
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
}

echo 'ok';
