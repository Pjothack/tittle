<?php
/**
 * Bootstrap for Tittle
 */

require_once 'vendor/autoload.php';

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// Clean up relative paths
$_SERVER['REQUEST_URI'] = '/' . substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])));

// Required CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' || isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    exit; // No content needs to be served
}

/**
 * Handler JSON formatted POST data
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) == 0) {
    $_POST = (array) @json_decode(file_get_contents("php://input"), true);
}

$app = new Application();
$app->mount('/', new \Tittle\Application\Routing());

// Handle errors
$app->error(
    function (\Exception $e, $code) use ($app) {
        $message = '';

        if ($e->getCode()) {
            $code = $e->getCode();
        }

        switch (get_class($e)) {
            case 'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException':
                $code = 405;
                $message = 'Method not allowed: '. $e->getMessage();
                break;
            case 'Symfony\Component\Routing\Exception\MissingMandatoryParametersException':
                $code = 400;
                $message = 'Missing mandatory parameter: '. $e->getMessage();
                break;
            case 'Symfony\Component\Routing\Exception\ResourceNotFoundException':
                $code = 404;
                $message = 'Resource not found';
                break;
            default:
                error_log("oliver: missing exception: {$e}");
                break;
        }

        return $app->json(
            [
                'errorCode' => $code,
                'errorMessage' => $message
            ],
            $code
        );
    }
);

$app->run();
