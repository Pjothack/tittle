<?php

namespace Tittle\Application\Controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class Events
{
    public function index(Request $request, Application $app)
    {
        return $app->json([]);
    }
}
