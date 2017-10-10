<?php
/**
 * Creates a new user
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;
use Facebook\FacebookRequest;

class CreateUser extends APIComponent {
  private $fields;
  private $method;
  private $required;
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    $this->fields = array(
     'password',
     'facebook_id',
     'first_name',
     'display',
     'last_name',
     'email'
    );

    if (isset($this->payload['facebook_id'])) {
      $this->method = 'social';
    } else {
      $this->method = 'manual';
    }

    if ($this->validate()) {
      // Sanitize the payload
      $this->payload = $this->sanitize($payload);

      // Check for duplicates
      if ($this->is_duplicate()) {
        $this->response = new Response([
          'body' => 'duplicate'
        ]);
        return;
      }

      // Social user's don't pass a password, but a random one will be generated
      // for them
      if ($this->method === 'social') {
        $this->payload['password'] = openssl_random_pseudo_bytes(10);
      }

      // Encrypt the password
      $this->payload['password'] = encrypt_password($this->payload['password']);

      // Geolocate
      $location = $this->get_location();
      if ($location) {
        $this->payload = array_merge($this->payload, $location);
      }

      // Store the user
      $store = $this->store();
      if (!$store) {
        return new Response([
          'body' => 'error'
        ]);
      }

      // Source the user's Facebook picture
      if (!empty($payload['facebook_picture'])) {
        $this->store_fb_profile_pic($payload['facebook_picture'], $store);
      }

      $this->response = new Response([
        'body' => 'success',
        'token' => create_token($store)
      ]);
    } else {
      $this->response = new Response([
        'body' => 'Invalid request'
      ]);
    }
  }

  /**
   * Stores the payload in the database
   *
   * @return The user's ID
   *
   * @return False
   */
  private function store () {
    // Unset system fields
    unset($this->payload['facebook_picture']);

    // Record the creation data
    $this->payload['created'] = date('U');

    // Collect the fields and values
    $fields = implode(", ", array_keys($this->payload));
    $values = "'" . implode("', '", $this->payload) . "'";

    // Query
    $sql = "INSERT INTO users ($fields) VALUES ($values)";
    $sql = $this->conn->query($sql);

    if ($sql === true) {
      return $this->conn->insert_id;
    } else {
      return false;
    }
  }

  /**
   * Stores a local copy of the user's Facebook profile picture
   *
   * This function should be called before the main ::store method as if
   * successful it will append the URL to the payload
   *
   * @param $source: A URL to the file
   * @param $user_id: The user's ID to attribute the upload to
   *
   * @return void
   */
  private function store_fb_profile_pic ($source, $user_id) {
    $temp = tempnam(sys_get_temp_dir(), 'TMP_');
    file_put_contents($temp, file_get_contents($source));
    $upload = new CreateFile([
      'user_id' => $user_id,
      'file' => $temp
    ]);
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
  private function validate () {
    $payload = $this->payload;
    // Unset fields that don't match the spec
    foreach (array_keys($payload) as $field) {
      if (!in_array($field, $this->fields)) {
        unset($payload[$field]);
      }
    }

    // If the user is signing up manually (I.e. not via a social network), then
    // we also need a password
    if ($this->method === 'manual' && empty($payload['password'])) {
      return false;
    }

    $this->payload = $payload;
    return true;
  }

  /**
   * Checks to see if the incoming request already exists in the DB
   *
   * @return bool
   */
  private function is_duplicate () {
    $email = $this->payload['email'];
    $facebook_id = '0';
    if (!empty($this->payload['facebook_id'])) {
      $facebook_id = $this->payload['facebook_id'];
    }
    $sql = "SELECT id FROM users WHERE email='$email' OR facebook_id='$facebook_id'";
    $sql = $this->conn->query($sql);

    return $sql->num_rows > 0;
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
