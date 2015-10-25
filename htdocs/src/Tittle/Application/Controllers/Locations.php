<?php

namespace Tittle\Application\Controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

use Tittle\DatabaseModels\Category as CategoryModel;
use Tittle\DatabaseModels\Location as LocationModel;
use Tittle\Environment;

class Locations
{
    public function add(Request $request, Application $app)
    {
        $title = $request->get('title');
        $category_name = $request->get('category');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $description = $request->get('description');

        if (!isset($title, $category_name, $latitude, $longitude, $description)) {
            throw new MissingMandatoryParametersException;
        }

        $location = new LocationModel;

        $location->title = $title;
        $location->category_id = CategoryModel::idByName($category_name);
        $location->latitude = $latitude;
        $location->longitude = $longitude;
        $location->description = $description;
        $location->save();

        return new Response('ok');
    }

    public function get(Request $request, Application $app)
    {
        $location_builder = LocationModel
            ::leftJoin('categories', 'locations.category_id', '=', 'categories.id')
            ->whereNotNull('category_id')
            ->orderBy('updated_at');

        if ($request->get('category')) {
            $location_builder = $location_builder->where('categories.name', $request->get('category'));
        }

        if ($request->get('with_description')) {
            $location_builder = $location_builder->where('categories.name', $request->get('category'));
        }

        if ($request->get('limit')) {
            $location_builder = $location_builder->limit($request->get('limit'));
        }

        $locations = $location_builder->with('category')->with('discounts')->with('trafficLevels')->get([
            'locations.id',
            'locations.title',
            'locations.latitude',
            'locations.longitude',
            'locations.title',
            'locations.description',
        ]);

        $stripped_locations = [];

        foreach ($locations as $loc) {
            $bare_traffic_levels = array_map(function ($data) {
                return $data['level'];
            }, $loc->trafficLevels->toArray());

            $average_traffic = count($bare_traffic_levels) > 0
                ? array_sum($bare_traffic_levels) / count($bare_traffic_levels)
                : -1;

            $returnable = [
                'id' => $loc->id,
                'title' => $loc->title,
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'category' => $loc->category->name,
                'discounts' => $loc->discounts,
                'traffic' => [
                    'average' => $average_traffic,
                    'count' => count($bare_traffic_levels),
                ],
            ];

            if ($request->get('with_description')) {
                $returnable['description'] = $loc->description;
            }

            $stripped_locations[] = $returnable;
        }

        return $app->json($stripped_locations);
    }

    public function update(Request $request, Application $app, $id)
    {
        $location = LocationModel::find($id);

        $title = $request->get('title');
        if (isset($title)) {
            $location->title = $title;
        }

        $category_name = $request->get('category_name');
        if (isset($category_game)) {
            $location->category_id = CategoryModel::idByName($category_name);
        }

        $latitude = $request->get('latitude');
        if (isset($latitude)) {
            $location->latitude = $latitude;
        }

        $longitude = $request->get('longitude');
        if (isset($longitude)) {
            $location->longitude = $longitude;
        }

        $description = $request->get('description');
        if (isset($description)) {
            $location->description = $description;
        }

        $location->save();

        return new Response('ok');
    }
}
