<?php
/*=============================================================================
 * INITIALIZATION
 * Imports, constants and application setup
 =============================================================================*/

// Import libraries (Slim framework, PHPMailer, etc)
require '../vendor/autoload.php';

// Import models
require '../classes/DB.php';
require '../classes/User.php';
require '../classes/Team.php';
require '../classes/Match.php';
require '../classes/Bet.php';
require '../classes/Stats.php';
require '../classes/Token.php';
require '../classes/Mail.php';

date_default_timezone_set('Europe/Lisbon');

/* Constants
 ******************************************************************************/
define('GOOGLE_RECAPTCHA_PRIVATE_KEY', '6Lf1UQkTAAAAANW3fDFp0JHdanyXxUxG_rIhqedd');
define('STATIC_URL', 'https://static.soccerwars.xyz');
define('ADMIN_TOKEN', 'superadmin');

/* Extend Slim class to always return JSON encoded responses
 ******************************************************************************/
class API extends \Slim\Slim {
    function render_json($data) {
        $this->response->headers->set('Content-Type', 'application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

/* Initialize the framework and its options
 ******************************************************************************/
$app = new API(['debug' => false]);
$app->add(new \CorsSlim\CorsSlim());



/*=============================================================================
 * HOOKS
 * Actions or checks to be performed before each request
 =============================================================================*/

$app->hook('slim.before.dispatch', function() use ($app) {
    // Check if the request was performed with the json content type header
    if ($app->request->getContentType() != 'application/json')
        throw new Exception("Invalid content type", 400);

    // Check the validity of a token sent by the user
    if (!(in_array($app->request->getResourceUri(), ['/login', '/users']) && $app->request->getMethod() == 'POST')) {
        if (!$app->request->headers->get('Token'))
            throw new Exception("Missing token", 400);
        else {
            $token = $app->request->headers->get('Token');
            if (!Token::Validate($token) && $token != ADMIN_TOKEN)
                throw new Exception("Invalid token", 400);
        }
    }
});



/*=============================================================================
 * ERROR HANDLERS
 * Wraps the response creation of any specified error, includes message and code
 =============================================================================*/

/* Render a custom JSON message whenever any exception is thrown
 ******************************************************************************/
$app->error(function(Exception $e) use ($app) {
    if ($e->getCode() !== 0)
        $app->response->setStatus($e->getCode());

    $app->render_json(["error" => $e->getMessage()]);
});

$app->notFound(function() {
    throw new Exception("This endpoint does not exist", 404);
});



/*=============================================================================
 * ROUTES
 * Main endpoints of the API
 =============================================================================*/

/* API root
 ******************************************************************************/
$app->get('/', function() use ($app) {
    $app->render_json(["message" => "SoccerWars API v0.1"]);
});

/* Check login credentials
 ******************************************************************************/
$app->post('/login', function() use ($app) {
    $data = json_decode($app->request->getBody(), true);

    if ($user_id = User::Login($data['email'], $data['password'])) {
        $user = User::Get($user_id);

        // Activate account if it's the first login and forbid inactive accounts
        if ($user->status == 'pending') {
            $user->setStatus('active');
        } else if ($user->status != 'active')
            throw new Exception("This account is not active", 401);

        // Return data
        $app->render_json([
            'name' => $user->name,
            'avatar' => $user->avatar,
            'token' => $user->getToken()
        ]);
    } else
        throw new Exception("Invalid credentials", 401);
});

/* Get current user info via his token
 ******************************************************************************/
$app->get('/me', function() use ($app) {
    $token = $app->request->headers->get('Token');
    if ($user = User::GetByToken($token)) {
        // Renew or create the user token upon login
        Token::Update($user->id);

        // Award badge after first login
        if (!isset($user->badges[0]['unlocked']) && strpos($app->request->getReferrer(), '/game/'))
            $user->awardBadge(1);

        $app->render_json($user);
    }
    else
        throw new Exception("Error obtaining profile", 500);
});

/* Get user by ID
 ******************************************************************************/
$app->get('/users/:id', function($id) use ($app) {
    if ($user = User::Get($id))
        $app->render_json($user);
    else
        throw new Exception("User not found", 404);
});

/* Get all users
 ******************************************************************************/
$app->get('/users', function() use ($app) {
    $response = User::GetAll();
    $app->render_json($response);
});

/* Create user
 ******************************************************************************/
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
        $recaptcha = new \ReCaptcha\ReCaptcha(GOOGLE_RECAPTCHA_PRIVATE_KEY);
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
            $message = "Greetings $name!\n\nYour password:\n\n" . $password;
            if (!Mail::send($email, $name, "Account created", $message))
                throw new Exception("Error sending email", 500);
            $app->render_json(["id" => $user_id]);
        } else {
            throw new Exception("Something went wrong!", 500);
        }
    }
});

/* Delete User
 ******************************************************************************/
$app->delete('/users/:id', function($id) use ($app) {
    if ($user = User::Get($id)) {

        // Set the user status to disabled instead of deleting it
        $user->setStatus('disabled');

        $app->render_json(["success" => true]);
    } else
        throw new Exception("User not found", 404);
});

/* Get stats by user
 ******************************************************************************/
$app->get('/users/:id/stats', function($id) use ($app) {
    $stats = Stats::GetByUser($id);
    $app->render_json($stats);
});

/* Get all teams
 ******************************************************************************/
$app->get('/teams', function() use ($app) {
    $response = Team::GetAll();
    $app->render_json($response);
});

/* Get match by ID
 ******************************************************************************/
$app->get('/matches/:id', function($id) use ($app) {
    if ($match = Match::Get($id))
        $app->render_json($match);
    else
        throw new Exception("Match not found", 404);
});

/* Get all matches
 ******************************************************************************/
$app->get('/matches', function() use ($app) {
    $response = array_slice(Match::GetAll(), 0, 50);
    $app->render_json($response);
});

/* Create match
 ******************************************************************************/
$app->post('/matches', function() use ($app) {
    $data = json_decode($app->request->getBody(), true);
    $team_1 = $data['team_1'];
    $team_2 = $data['team_2'];
    $start_time = $data['start_time'];
    $end_time = $data['end_time'];

    $match = new Match();
    $match->team_1 = $team_1;
    $match->team_2 = $team_2;
    $match->start_time = $start_time;
    $match->end_time = $end_time;

    // Create the match
    if ($match_id = $match->Create())
        $app->render_json(["id" => $match_id]);
    else
        throw new Exception("Error creating the match", 400);
});

/* Insert comment
 ******************************************************************************/
$app->post('/matches/:id/comments', function($id) use ($app) {
    $data = json_decode($app->request->getBody(), true);
    $text = $data['text'];
    $user = User::GetByToken($app->request->headers->get('token'));

    if (empty($text))
        throw new Exception("No text in comment", 400);

    // Insert the comment
    $match = Match::Get($id);
    if ($comment_id = $match->insertComment($user->id, $text)) {
        $user->awardBadge(10);
        $app->render_json(['id' => $comment_id]);
    } else
        throw new Exception("Error inserting the comment", 400);
});

/* Get comments
 ******************************************************************************/
$app->get('/matches/:id/comments', function($id) use ($app) {
    $match = Match::Get($id);
    $response = $match->getComments();
    $app->render_json($response);
});

/* Place bet
 ******************************************************************************/
$app->post('/matches/:id/bet', function($id) use ($app) {
    $data = json_decode($app->request->getBody(), true);

    $type = $data['type'];
    $team = $data['team'];
    $user = User::GetByToken($app->request->headers->get('token'));

    // Create the bet
    $bet = new Bet();
    $bet->user_id = $user->id;
    $bet->match_id = $id;
    $bet->type = $type;
    $bet->team = $team;

    if ($type === 1) {
        $bet->points_simple = $data['points_simple'];
    } elseif ($type === 2) {
        $bet->points_goals = $data['points_goals'];
        $bet->points_yellowcards = $data['points_yellowcards'];
        $bet->points_redcards = $data['points_redcards'];
        $bet->points_defenses = $data['points_defenses'];
    }

    if ($bet_id = $bet->Create()) {
        $app->render_json(['id' => $bet_id]);
    } else
        throw new Exception("Bet not allowed", 400);
});

/* Get bets
 ******************************************************************************/
$app->get('/bets', function() use ($app) {
    $user = User::GetByToken($app->request->headers->get('token'));
    $response = Bet::GetByUser($user->id);
    $app->render_json($response);
});

/* Get global stats
 ******************************************************************************/
$app->get('/stats', function() use ($app) {
    $stats = Stats::Get();
    $app->render_json($stats);
});

/* RUN THE APPLICATION
 ******************************************************************************/
$app->run();