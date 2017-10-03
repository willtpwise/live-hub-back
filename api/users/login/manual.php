<?php
/**
 * Handles the manual login process
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;

class ManualLogin extends APIComponent {
  public $payload;
  public $user;
  public $status;
  private $conn;

  function __construct ($payload) {
    $this->status = false;
    $this->conn = connect();
    $this->user = false;

    if ($this->validate($payload)) {
      // Sanitize the payload
      $this->payload = $this->sanitize($payload);

      // Lookup the user
      $this->user = $this->lookup();

      // Compare the encrypted passwords
      $this->status = ($this->user && $this->compare());
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
    $required = ['email', 'password'];

    // Check that all required fields exist
    foreach ($required as $field) {
      if (empty($query[$field])) {
        return false;
      }
    }

    return true;
  }

  /**
   * Lookups the incoming user
   *
   * @return The user's ID and encrypted password on success
   * @return False if the user couldn't be found
   */
  private function lookup () {
    $email = $this->payload['email'];
    $sql = "SELECT id, password FROM users WHERE email = '$email'";
    $result = $this->conn->query($sql);
    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    } else {
      return false;
    }
  }

  /**
   * Compares the two passwords
   *
   * @return True / false indicating whether they match
   */
  private function compare () {
    return password_verify($this->payload['password'], $this->user['password']);
  }
}
