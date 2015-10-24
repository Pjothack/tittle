<?php

namespace Tittle\Application\Controllers;

use Illuminate\Database\Capsule\Manager as DB;

use Symfony\Component\HttpFoundation\Response;

class Status
{
    public function status()
    {
        $thing = DB::table('test')->select(DB::raw('*'))->get()[0];

        return new Response($thing['content']);
    }
}
