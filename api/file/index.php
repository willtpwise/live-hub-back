<?php
/**
 * Lists a single user
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class GetUser extends APIComponent {
  private $query;

  function __construct ($query) {
    $this->query = $query;
    $this->lookup();
  }

  private function lookup () {
    if (empty($this->query['id']) && empty($this->query['username'])) {
      $this->response = new Response(array(
        'header' => 400
      ));
      return;
    }

    $query_term = empty($this->query['id']) ? 'username' : 'id';
    $query_val = $this->query[$query_term];
    
    $conn = connect();
    $sql = "SELECT * FROM users WHERE $query_term = '$query_val'";
    $sql = $conn->query($sql);

    if ($sql) {

      $user = $sql->fetch_assoc();
      unset($user['password']);

      $this->response = new Response(array(
        'body' => $user
      ));

    } else {

      $this->response =  new Response(array(
        'header' => 404
      ));

    }

  }
}
