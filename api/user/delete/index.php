<?php
/**
 * Deletes an exisiting user based on the passed user id
 *
 * The user must be signed in or an admin
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class DeleteUser {
  public $user_id;
  function __construct ($user_id) {
    $this->user_id = $user_id;
  }

  private function query () {
    $conn = connect();
    $user_id = $this->user_id;
    $sql = "DELETE FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result) {
      return new Response(array(
        'body' => 'success'
      ));
    } else {
      return new Response(array(
        'header' => '500'
      ));
    }
  }
}
