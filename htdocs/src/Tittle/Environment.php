<?php

namespace Tittle;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * All kinds of miscellaneous helpers and environment variables
 */
abstract class Environment
{
    protected static $eloquent_initialized = false;

    /**
     * Initialize Eloquent standalone capsule
     */
    public static function initializeEloquent()
    {
        // Prevent double loading
        if (self::$eloquent_initialized) {
            return;
        }

        $env_db_configuration = json_decode($_ENV['VCAP_SERVICES'], true)['cleardb'][0]['credentials'];
        $configuration = array_merge(
            [
                'host' => $env_db_configuration['hostname'],
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'driver' => 'mysql',
                'database' => $env_db_configuration['name'],
                'username' => $env_db_configuration['username'],
                'password' => $env_db_configuration['password'],
                'prefix' => '',
            ]
        );

        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($configuration);
        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        self::$eloquent_initialized = true;
    }

    public static function terminateEloquent()
    {
        DB::disconnect();
        self::$eloquent_initialized = false;
    }
}
