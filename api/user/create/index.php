<?php
/**
 * Creates a new user using the passed post data
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class CreateUser extends APIComponent {
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
      $this->response = $this->query();
    } else {
      $this->response = new Response(array(
        'header' => 400
      ));
    }
  }

  private function query () {
    $fields = implode(", ", array_keys($this->payload));
    $values = "'" . implode("', '", $this->payload) . "'";
    $conn = connect();
    $sql = "INSERT INTO users ($fields) VALUES ($values)";
    $result = $conn->query($sql);

    if ($result) {
      return new Response(array(
        'body' => $conn->insert_id,
        'token' => token($conn->insert_id, $this->payload['username'])
      ));
    } else {
      return new Response(array(
        'header' => 500
      ));
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
    $payload = array();
    foreach ($this->fields as $field) {
      $payload[$field] = filter_input(INPUT_POST, $field, FILTER_SANITIZE_STRING);
    }
    $this->payload = $payload;

    // Hash the password
    $this->payload['password'] = password_hash($this->payload['password'], PASSWORD_DEFAULT);

    return true;
  }
}
