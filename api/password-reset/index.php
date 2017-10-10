<?php
/**
 * Submits a new password reset request
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class PasswordReset extends APIComponent {
  public $payload;
  public $conn;

  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    // Validate the incoming request
    if (!$this->validate()) {
      $this->response = new Response(array(
        'body' => 'Invalid request'
      ));
      return;
    }

    // Query the database in search for the user
    $user_id = $this->query($payload['email']);

    if (!$user_id) {
      // Response the same way as a successful request
      // The user shouldn't learn that that email doesn't exist
      $this->response = new Response(array(
        'body' => 'success'
      ));
      return;
    }

    // Generate a random challenge code
    $challenge = $this->generate_challenge();

    // Log the request
    $this->log($user_id, $challenge);

    // Email the user
    $this->notify($payload['email'], $challenge);

    // Response
    $this->response = new Response(array(
      'body' => 'success'
    ));
  }

  /**
   * Checks that the request is valid
   *
   * @return True / False, indicating whether the request should be accepted
   */
  public function validate () {
    // Check that the request contains an email address
    if (empty($this->payload['email'])) {
      return false;
    }

    return true;
  }

  /**
   * Queries the database and checks if the user exists
   *
   * @param $email: The user's email address
   *
   * @return The user's ID
   * @return False if the user is unknown
   */
  public function query ($email) {
    $user = "SELECT id FROM users WHERE email='$email'";
    $user = $this->conn->query($user);
    if ($user) {
      $user = $user->fetch_assoc();
      return $user['id'];
    } else {
      return false;
    }
  }

  /**
   * Notifies the user by email
   *
   * @param $email: The email to send to
   * @param $challenge: The challenge code
   *
   * @return void
   */
  public function notify ($email, $challenge) {
    $email = new Notify(array(
      'to' => $email,
      'template' => 'password_reset',
      'data' => array(
        'challenge' => $challenge
      )
    ));
  }

  /**
   * Logs the request to the DB and stores the challenge code
   *
   * @param $user_id: The user requesting the reset
   * @param $challenge: The challenge code
   */
  public function log($user_id, $challenge) {
    // Dump any unanswered requests
    $dump = "DELETE FROM password_reset WHERE user_id='$user_id'";
    $this->conn->query($dump);

    // Log the new request
    $insert = "INSERT INTO password_reset (user_id, challenge) VALUES ('$user_id', '$challenge')";
    return $this->conn->query($insert);
  }

  /**
   * Generates a challenge code
   *
   * @return string : The challenge code. Less than 10 characters.
   */
  public function generate_challenge() {
    $length = 6;
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz';
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
      throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; ++$i) {
      $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
  }
}
