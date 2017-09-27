<?php
/**
 * Load API methods then handles incoming api requests
 */
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Authorization, Content-type');

require_once (__DIR__ . '/auth/token.php');
require_once (__DIR__ . '/auth/verify.php');
require_once (__DIR__ . '/utilities/response.php');
require_once (__DIR__ . '/utilities/connect.php');
require_once (__DIR__ . '/api/api-component.php');
require_once (__DIR__ . '/utilities/request-data.php');

// User API
require_once (__DIR__ . '/api/user/index.php');
require_once (__DIR__ . '/api/user/create/index.php');

// File API
require_once (__DIR__ . '/api/file/create/index.php');
require_once (__DIR__ . '/api/file/delete/index.php');

$routes = array(
  '/user/' => 'GetUser',
  '/user/create/' => 'CreateUser',
  '/file/create/' => 'CreateFile',
  '/file/delete/' => 'DeleteFile'
);

if (isset($routes[REQUEST_URI])) {

  $api = new $routes[REQUEST_URI]($_REQUEST);
  $api->response->send();

} else {

  $error = new Response(array(
    'header' => 404
  ));
  $error->send();

}
