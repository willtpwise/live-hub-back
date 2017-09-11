<?php
/**
 * Lists a single user
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

require_once (__DIR__ . '/../../database/connect.php');
require_once (__DIR__ . '/../../auth/token.php');

class User {
  private $query;

  function __construct ($query) {
    $this->query = $query;
  }

  private function lookup () {
    if (empty($this->query['id']) && empty($this->query['username'])) {
      return array(
        'status' => false,
        'header' => 'HTTP/1.0 400 Bad Request'
      );
    }

    $query_term = empty($this->query['id']) ? 'username' : 'id';
    $query_val = $this->query[$term];

    $query_val = filter_input($this->query, $this->query['term'], FILTER_SANITIZE_STRING);
    $conn = connect();
    $sql = "SELECT * FROM users WHERE $query_term = '$query_val'";
    $sql = $conn->query($sql);

    if ($sql) {

      $user = $sql->fetch_assoc();
      unset($user['password']);

      return array(
        'status' => true,
        'header' => 'Content-type: application/json',
        'data' => json_encode($user)
      );

    } else {

      return array(
        'status' => false,
        'header' => 'HTTP/1.0 404 Not Found'
      );

    }

  }
}

$request = new Request();
if ($request->isPost()) {
  $User = new User($_POST);
  $response = $User->lookup();
  if ($response['status']) {
    header($response['header']);
    echo $response['data'];
  }
}
