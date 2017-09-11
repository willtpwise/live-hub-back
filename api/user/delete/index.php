<?php
/**
 * Deletes an exisiting user based on the passed user id
 *
 * The user must be signed in or an admin
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

require_once (__DIR__ . '/../../database/connect.php');
require_once (__DIR__ . '/../../auth/verify.php');

if (isset($_GET['id']) && verify_token()) {

  $user_id = $_GET['id'];

  $conn = connect();
  $sql = "DELETE FROM users WHERE id = $user_id";
  $result = $conn->query($sql);

  if ($result) {
    echo "success";
  } else {
    header('HTTP/1.0 500 Internal Server Error');
  }

} else {

  header('HTTP/1.0 400 Bad Request');

}
