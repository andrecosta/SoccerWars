<?php
// Define authorized application keys which can make requests to this API
define('APP_KEYS', serialize(['webapp', 'cron', 'mobile']));

require 'classes/User.php';
require 'vendor/autoload.php';

// Extend Slim class to provide a general method to return a json encoded response
class API extends \Slim\Slim {
    function render_json($data) {
        $this->response->headers->set('Content-Type', 'application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

// Initialize the application
$app = new API(['debug' => false]);
$app->add(new \CorsSlim\CorsSlim(array()));


/*==============================================================================
 * HOOKS
 * Actions or checks to be performed before each request
 ==============================================================================*/

$app->hook('slim.before.dispatch', function() use ($app) {
    // Check if the request was performed with the json content type header
    if ($app->request->getContentType() != 'application/json')
        throw new Exception("Invalid content type", 400);

    // Check if an authorized app key is present in the request header
    if (!in_array($app->request->headers->get('Secret'), unserialize(APP_KEYS)))
        throw new Exception("Invalid app token", 400);
});


/*==============================================================================
 * ERROR HANDLERS
 * Wraps the response creation of any specified error, includes message and code
 ==============================================================================*/

$app->error(function(Exception $e) use ($app) {
    if ($e->getCode() !== 0)
        $app->response->setStatus($e->getCode());

    $app->render_json(["error" => $e->getMessage()]);
});

$app->notFound(function() {
    throw new Exception("This endpoint does not exist", 404);
});


/*==============================================================================
 * ROUTES
 * Main endpoints of the API
 ==============================================================================*/

// API index
$app->get('/', function() use ($app) {
    $app->response->setStatus(200);
    $app->render_json(["message" => "API v1"]);
});

// Check login credentials
$app->post('/login', function() use ($app) {
    $data = json_decode($app->request->getBody(), true);
    $user = User::Login($data['email'], $data['pw_hash']);

    if ($user)
        $response = $user;
    else
        throw new Exception("Invalid credentials", 403);

    $app->render_json($response);
});

// Get User by ID
$app->get('/users/:id', function($id) use ($app) {
    if ($user = User::Get($id))
        $response = $user;
    else
        throw new Exception("User not found", 404);

    $app->render_json($response);
});

// Get all Users
$app->get('/users', function() use ($app) {
    $response = User::GetAll();

    $app->render_json($response);
});

// Create User
$app->post('/users', function() use ($app) {
    // $data = json_decode($app->request->getBody());
    // return id or false
});

// Update User
$app->patch('/users/:id', function($id) use ($app) {
    // return bool
});

// Delete User
$app->delete('/users/:id', function($id) use ($app) {
    // disable only!
    // return bool
});

$app->run();