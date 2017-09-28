<?php
/**
 * Updates an existing user
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class UpdateUser extends APIComponent {
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    if (!$this->is_permitted()) {
      $this->response = new Response([
        'header' => 401
      ]);
      return;
    }

    if ($this->validate($payload)) {
      // Encrypt the password
      if (!empty($this->payload['password'])) {
        $this->payload['password'] = $this->encrypt($this->payload['password']);
      }

      // Dump any existing meta
      $this->dump_meta();

      // Store the data
      $this->store_user();
      $this->store_meta();

      $this->response = new Response([
        'body' => true
      ]);
    } else {
      $this->response = new Response([
        'header' => 400
      ]);
    }
  }

  /**
   * Checks to see if the request is allowed to update the user
   *
   * @return true / false
   */
  private function is_permitted () {
    if (REQUEST_USER === intval($this->payload['id'])) {
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
    $user_id = intval($this->payload['id']);
    $sql = "DELETE FROM meta WHERE user_id = $user_id";
    return $this->conn->query($sql);
  }

  /**
   * Stores the user payload in the database
   *
   * @return void
   */
  private function store_user () {
    // Seperate the meta payload from the user payload
    $payload = $this->payload;
    unset($payload['meta']);

    // Unset the id property
    $user_id = $payload['id'];
    unset($payload['id']);

    // Prevent passwords being overwritten with empty strings
    if (isset($payload['password']) && empty($payload['password'])) {
      unset($payload['password']);
    }

    // Sanitize the data
    $payload = $this->sanitize($payload);

    // Collect the fields and values
    $values = [];
    $keys = array_keys($payload);
    foreach ($keys as $key) {
      $val = $payload[$key];
      $values[] = "$key='$val'";
    }
    $values = implode(', ', $values);

    // Query
    $sql = "UPDATE users SET $values WHERE id=$user_id";

    return $this->conn->query($sql);
  }

  /**
   * Stores the meta payload in the database
   *
   * @return void
   */
  private function store_meta () {
    if (empty($this->payload['meta'])) {
      return;
    }

    // Seperate the meta payload from the user payload
    $payload = $this->payload['meta'];

    foreach (array_keys($payload) as $category_name) {
      foreach ($payload[$category_name] as $meta) {
        // Append the user's id and the meta category
        $meta['user_id'] = $this->payload['id'];
        $meta['category'] = $category_name;

        // Sanitize the data
        $meta = $this->sanitize($meta);

        // Collect the fields and values
        $fields = implode(", ", array_keys($meta));
        $values = "'" . implode("', '", $meta) . "'";

        // Query
        $sql = "INSERT INTO meta ($fields) VALUES ($values)";
        $this->conn->query($sql);
      }
    }
  }

  /**
   * Encrypt the password
   *
   * @param $password: The password to be encrypted
   *
   * @return The encrypted password
   */
  private function encrypt ($password) {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  /**
   * Cleans (sanitizes) the passed string
   *
   * @param $string: The possibly dirty string
   *
   * @return The cleaned string
   */
  public function clean ($string) {
    return $this->conn->real_escape_string($string);
  }

  /**
   * Sanitizes the passed array
   *
   * @param $query: The payload for the database as an array
   *
   * @return The sanitized array
   */
  private function sanitize ($query) {
    return array_map(array($this, 'clean'), $query);
  }

  /**
   * Validates that the incoming data is correct
   *
   * @param $query : The payload for the database
   *
   * @return bool
   */
  private function validate ($query) {
    // Accepted fields
    $user_fields = [
      'id',
      'first_name',
      'last_name',
      'bio',
      'email',
      'street_number',
      'route',
      'locality',
      'administrative_area_level_1',
      'country',
      'postal_code',
      'lat',
      'lng',
      'meta',
      'password'
    ];

    // Check that the passed fields are available in the db
    foreach (array_keys($query) as $field) {
      if (!in_array($field, $user_fields)) {
        return false;
      }
    }

    return true;
  }
}
