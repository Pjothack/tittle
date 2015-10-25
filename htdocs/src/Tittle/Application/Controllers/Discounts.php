<?php

namespace Tittle\Application\Controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

use Tittle\DatabaseModels\Discount as DiscountModel;

class Discounts
{
    public function getByLocation(Request $request, Application $app, $id)
    {
        $discounts_builder = DiscountModel::where('location_id', $id);

        if ($request->get('active')) {
            $discounts_builder = $discounts_builder
                ->where('effective_from', '<', date('Y-m-d H:i:s'))
                ->where('effective_to', '>', date('Y-m-d H:i:s'));
        }

        $discounts = $discounts_builder->get([
            'id',
            'effective_from',
            'effective_to',
            'description',
        ]);

        return $app->json($discounts);
    }

    public function addToLocation(Request $request, Application $app, $id)
    {
        $effective_from = $request->get('effective_from');
        if (!isset($effective_from)) {
            throw new MissingMandatoryParametersException;
        }

        $effective_to = $request->get('effective_to');
        if (!isset($effective_to)) {
            throw new MissingMandatoryParametersException;
        }

        $description = $request->get('description');
        if (!isset($description)) {
            throw new MissingMandatoryParametersException;
        }

        $discount = new DiscountModel;
        $discount->location_id = $id;
        $discount->effective_from = date('Y-m-d H:i:s', $effective_from);
        $discount->effective_to = date('Y-m-d H:i:s', $effective_to);
        $discount->description = $description;
        $discount->save();

        return new Response('ok');
    }
}
