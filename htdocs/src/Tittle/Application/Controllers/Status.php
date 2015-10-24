<?php

namespace Tittle\Application\Controllers;

use Illuminate\Database\Capsule\Manager as DB;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class Status
{
    public function index(Request $request, Application $app)
    {
        $thing = DB::table('test')->select(DB::raw('*'))->get()[0];
        $database_ok = $thing && !empty($thing['content']);

        return $app->json(['database_ok' => $database_ok]);
    }
}
