<?php
/**
 * Updates an existing user using the passed post data
 *
 * The user must be signed in or an admin
 */

// header('Access-Control-Allow-Headers: Authorization');

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

require_once (__DIR__ . '/validate.php');
require_once (__DIR__ . '/../../database/connect.php');
require_once (__DIR__ . '/../../auth/token.php');

$fields = array(
 'id',
 'username',
 'first_name',
 'password'
);

$payload = validate($fields);

if ($payload) {

  $values = array();
  foreach (array_keys($payload) as $field) {
    $values[] = $field . "='" . $payload[$field] . "'";
  }
  $values = implode(', ', $values);

  $user_id = $payload['id'];
  $user_name = $payload['username'];

  $conn = connect();
  $sql = "UPDATE users SET $values WHERE id = $user_id";
  $result = $conn->query($sql);

  if ($result) {
    echo token($user_id, $user_name);
  } else {
    header('HTTP/1.0 500 Internal Server Error');
  }

} else {

  header('HTTP/1.0 400 Bad Request');

}
