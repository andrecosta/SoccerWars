<?php
require '../classes/User.php';
require '../vendor/autoload.php';

// Google Recaptcha private key
define('RECAPTCHA_KEY', '6Lf1UQkTAAAAANW3fDFp0JHdanyXxUxG_rIhqedd');

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

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     host="localhost:5000",
 *     schemes={"http", "https"},
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @SWG\Info(
 *         version="0.0.1",
 *         title="SoccerWars API",
 *         description="Common API to interface with web and mobile applications and interact with the database",
 *         @SWG\Contact(name="Fabio Costa"),
 *     ),
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"error"},
 *         @SWG\Property(
 *             property="error",
 *             type="string"
 *         )
 *     )
 * )
 */


/*==============================================================================
 * HOOKS
 * Actions or checks to be performed before each request
 ==============================================================================*/

$app->hook('slim.before.dispatch', function() use ($app) {
    // Check if the request was performed with the json content type header
    if ($app->request->getContentType() != 'application/json')
        throw new Exception("Invalid content type", 400);

    // Check if an authorized app key is present in the request header
    /*if (!in_array($app->request->headers->get('Secret'), unserialize(APP_KEYS)))
        throw new Exception("Invalid app token", 400);*/
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

    if ($user = User::Login($data['email'], $data['password'])) {
        if ($user->status == 'pending') {
            $user->setStatus('active');
        } else if ($user->status != 'active')
            throw new Exception("This account is not active", 401);

        $app->render_json([
            'name' => $user->name,
            'token' => ""
        ]);
    } else
        throw new Exception("Invalid credentials", 401);
});

// Get User by ID
$app->get('/users/:id', function($id) use ($app) {
    if ($user = User::Get($id))
        $app->render_json($user);
    else
        throw new Exception("User not found", 404);
});

// Get all users
$app->get('/users', function() use ($app) {
    $response = User::GetAll();

    $app->render_json($response);
});

// Create User
$app->post('/users', function() use ($app) {
    $data = json_decode($app->request->getBody(), true);
    $email = $data['email'];
    $name = $data['name'];
    $captcha = $data['captcha'];

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("No valid email address was supplied", 400);
    } else if (!$name /* +other restrictions */) {
        throw new Exception("Invalid character name", 400);
    } elseif (!$captcha) {
        throw new Exception("Captcha was not provided", 400);
    } else {
        // Validate Captcha
        $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_KEY);
        $verify = $recaptcha->verify($captcha, $app->request->getIp());
        if (!$verify->isSuccess())
            throw new Exception("Humanity not confirmed", 400);

        // Create user
        $user = new User();
        $user->email = $email;
        $user->name = $name;
        $user->avatar = '';
        $password = $user->createRandomPassword(6);
        if ($user_id = $user->Create()) {
            //if (@mail($email, "SoccerWars Account", "Password: " . $password))
            //    throw new Exception("Error sending email", 500);
            $app->render_json(["id" => $user_id]);
        } else {
            throw new Exception("Something went wrong!", 500);
        }
    }
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

// API Documentation
$app->get('/swagger', function() use ($app) {
    $swagger = \Swagger\scan(['.', '../classes/']);
    echo $swagger;
});

$app->run();