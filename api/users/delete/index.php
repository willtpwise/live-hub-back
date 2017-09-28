<?php
/**
 * Deletes an existing user
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class DeleteUser extends APIComponent {
  private $user_id;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();

    if (!isset($payload['id'])) {
      $this->response = new Response([
        'header' => 400
      ]);
      return;
    }

    $this->user_id = $payload['id'];

    if (!$this->is_permitted()) {
      $this->response = new Response([
        'header' => 401
      ]);
      return;
    }

    // Dump any existing meta
    $this->dump_meta();

    // Dump the user
    $this->dump_user();

    $this->response = new Response([
      'body' => 'success'
    ]);
  }

  /**
   * Checks to see if the request is allowed to update the user
   *
   * @return true / false
   */
  private function is_permitted () {
    if (REQUEST_USER === intval($this->user_id)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Dumps all meta results for the user
   *
   * @return true/false on success
   */
  private function dump_meta () {
    $user_id = intval($this->user_id);
    $sql = "DELETE FROM meta WHERE user_id = $user_id";
    return $this->conn->query($sql);
  }

  /**
   * Dumps the user profile
   *
   * @return true/false on success
   */
  private function dump_user () {
    $user_id = intval($this->user_id);
    $sql = "DELETE FROM users WHERE user_id = $user_id";
    return $this->conn->query($sql);
  }
}
