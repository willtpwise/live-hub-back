<?php
/**
 * Creates a new user using the passed post data
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

require_once (__DIR__ . '/../../database/connect.php');
require_once (__DIR__ . '/../../auth/token.php');

class CreateUser {
  public $fields;
  public $required;
  public $payload;

  function __construct ($payload) {
    $this->payload = $payload;

    $this->fields = array(
     'username',
     'password',
     'first_name',
     'last_name',
     'email',
     'address_lat',
     'address_lng',
     'address_street',
     'address_state',
     'address_suburb',
     'address_country',
     'address_post_code'
    );

    $this->required = array(
     'username',
     'password',
     'first_name',
     'last_name',
     'email'
    );

    if ($this->validate()) {
      $this->query();
    } else {
      header('HTTP/1.0 400 Bad Request');
    }
  }

  private function query () {
    $fields = implode(", ", array_keys($this->payload));
    $values = "'" . implode("', '", $this->payload) . "'";
    $conn = connect();
    $sql = "INSERT INTO users ($fields) VALUES ($values)";
    $result = $conn->query($sql);

    if ($result) {
      echo token($conn->insert_id, $this->payload['password']);
    } else {
      header('HTTP/1.0 500 Internal Server Error');
    }
  }

  private function validate () {
    // Check that all required fields exist
    foreach ($this->required as $field) {
      if (empty($_POST[$field])) {
        die($field);
        return false;
      }
    }

    // Sanitize and store the payload
    foreach ($this->fields as $field) {
      $this->payload[$field] = filter_input(INPUT_POST, $field, FILTER_SANITIZE_STRING);
    }

    // Hash the password
    $this->payload['password'] = password_hash($this->payload['password'], PASSWORD_DEFAULT);

    return true;
  }
}

$request = new Request();
if ($request->isPost()) {
  $CreateUser = new CreateUser($_POST);
}
