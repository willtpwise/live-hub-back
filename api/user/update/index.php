<?php
/**
 * Updates an existing user using the passed post data
 *
 * The user must be signed in or an admin
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class UpdateUser extends APIComponent {
  public $fields;
  public $required;
  public $payload;

  function __construct ($payload) {
    $this->payload = $payload;

    $this->fields = array(
     'password',
     'first_name',
     'last_name',
     'nickname',
     'email',
     'lat',
     'lng',
     'street_number',
     'route',
     'locality',
     'administrative_area_level_1',
     'country',
     'postal_code'
    );

    $this->required = array(
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
    $values = array();
    foreach (array_keys($this->payload) as $field) {
      $values[] = $field . "='" . $this->payload[$field] . "'";
    }
    $values = implode(', ', $values);

    $user_id = $this->payload['id'];
    $user_name = $this->payload['username'];

    $conn = connect();
    $sql = "UPDATE users SET $values WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result) {
      return new Response(array(
        'token' => token($conn->insert_id, $this->payload['password'])
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
        return false;
      }
    }

    // Sanitize and store the payload
    foreach ($this->fields as $field) {
      $this->payload[$field] = filter_input(INPUT_POST, $field, FILTER_SANITIZE_STRING);
    }

    // Hash the password
    if (isset($this->payload['password'])) {
      if (empty($this->payload['password'])) {
        unset($this->payload['password']);
      } else {
        $this->payload['password'] = password_hash($this->payload['password'], PASSWORD_DEFAULT);
      }
    }

    return true;
  }
}
