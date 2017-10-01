<?php
/**
 * Creates a new user
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;

class CreateUser extends APIComponent {
  private $fields;
  private $required;
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();

    $this->fields = array(
     'password',
     'first_name',
     'last_name',
     'email'
    );

    $this->required = array(
     'password',
     'first_name',
     'last_name',
     'email'
    );

    if ($this->validate($payload)) {
      // Sanitize the payload
      $this->payload = $this->sanitize($payload);

      // Encrypt the password
      $this->payload['password'] = $this->encrypt($this->payload['password']);

      // Geolocate
      $location = $this->get_location();
      if ($location) {
        $this->payload = array_merge($this->payload, $location);
      }

      // Store the user
      $this->response = $this->store();
    } else {
      $this->response = new Response([
        'body' => 'Invalid request'
      ]);
    }
  }

  /**
   * Stores the payload in the database
   *
   * @return A Response object to be sent to the user. The respone object will
   * contain the token field with a valid JWT
   *
   * @return If the query fails, a Response object with a 500 header
   */
  private function store () {
    // Collect the fields and values
    $fields = implode(", ", array_keys($this->payload));
    $values = "'" . implode("', '", $this->payload) . "'";

    // Query
    $sql = "INSERT INTO users ($fields) VALUES ($values)";

    if ($this->conn->query($sql) === true) {
      return new Response([
        'body' => 'success',
        'token' => create_token($this->conn->insert_id)
      ]);
    } else {
      return new Response([
        'body' => 'invalid request'
      ]);
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
    // Check that all required fields exist
    foreach ($this->required as $field) {
      if (empty($query[$field])) {
        return false;
      }
    }

    // Record the creation data
    $this->payload['created'] = date('U');

    return true;
  }

  /**
   * Returns the user's location based off their IP
   *
   * @return An array with lat, lng properties
   * @return false if the user's IP wasn't found in the DB
   */
  private function get_location () {
    try {
      // MaxMind GeoIP lookup
      // See: http://maxmind.github.io/GeoIP2-php/
      $reader = new Reader('assets/GeoLite2-City.mmdb');
      $record = $reader->city($_SERVER['REMOTE_ADDR']);
      return [
        'lat' => $record->location->latitude,
        'lng' => $record->location->longitude
      ];
    } catch (Exception $e) {
      return false;
    }
  }
}
