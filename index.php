<?php
/**
 * Load API methods then handles incoming api requests
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-type');

require_once (__DIR__ . '/auth/token.php');
require_once (__DIR__ . '/auth/verify.php');
require_once (__DIR__ . '/utilities/response.php');
require_once (__DIR__ . '/utilities/connect.php');
require_once (__DIR__ . '/utilities/request-data.php');
require_once (__DIR__ . '/utilities/front-end.php');
require_once (__DIR__ . '/utilities/encrypt-password.php');
require_once (__DIR__ . '/api/api-component.php');

// User API
require_once (__DIR__ . '/api/users/index.php');
require_once (__DIR__ . '/api/users/create/index.php');
require_once (__DIR__ . '/api/users/update/index.php');
require_once (__DIR__ . '/api/users/delete/index.php');
require_once (__DIR__ . '/api/users/login/index.php');

// File API
require_once (__DIR__ . '/api/file/index.php');
require_once (__DIR__ . '/api/file/create/index.php');
require_once (__DIR__ . '/api/file/delete/index.php');

// Notify API
require_once (__DIR__ . '/api/notify/index.php');

// Password reset API
require_once (__DIR__ . '/api/password-reset/index.php');
require_once (__DIR__ . '/api/password-reset/validate-challenge/index.php');

$routes = [
  // User API
  '/users/'          => 'GetUsers',
  '/users/create/'   => 'CreateUser',
  '/users/update/'   => 'UpdateUser',
  '/users/delete/'   => 'DeleteUser',
  '/users/login/'    => 'LoginUser',

  // File API
  '/file/create/'    => 'CreateFile',

  // Password Reset API
  '/password-reset/' => 'PasswordReset',
  '/password-reset/validate-challenge/' => 'PasswordChallenge'
];

if (isset($routes[REQUEST_URI])) {

  $api = new $routes[REQUEST_URI]($_POST);
  $api->response->send();

} else {

  $error = new Response([
    'header' => 404
  ]);
  $error->send();

}
