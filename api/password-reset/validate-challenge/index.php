<?php
/**
 * Validates the passed challenge
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class PasswordChallenge extends APIComponent {
  public $payload;
  public $conn;
  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    // The challenge lifetime (60 minutes)
    $this->lifetime = 60000 * 60;

    // Validate the incoming request
    if (!$this->validate()) {
      $this->response = new Response(array(
        'body' => false
      ));
      return;
    }

    // Validate the challenge
    $challenge_result = $this->validate_challenge(
      $payload['id'],
      $payload['challenge']
    );

    // Negotiate the challenge result
    if ($challenge_result === 'expired') { // The challenge has expired

      $this->response = new Response(array(
        'body' => 'expired'
      ));

    } else if (!$challenge_result) { // The challenge was not successful

      $this->response = new Response(array(
        'body' => false
      ));

    } else { // The challenge was successful

      // Dump the challenge record
      $this->dump_challenge_records($payload['id']);

      // Reset the password
      $this->set_new_password($payload['id'], $payload['password']);

      // Validate the user's JWT
      $this->response = new Response(array(
        'body' => true,
        'token' => create_token($payload['id'])
      ));
    }
  }

  /**
   * Dumps old challenge records
   *
   * @param $user_id : The user who's challenges should be revoked
   */
  private function dump_challenge_records ($user_id) {
    $sql = "DELETE FROM password_reset WHERE user_id='$user_id'";
    return $this->conn->query($sql);
  }


  /**
   * Checks that the request is valid
   *
   * @return True / False, indicating whether the request should be accepted
   */
  public function validate () {
    $required = [
      'id',
      'challenge',
      'password'
    ];

    foreach ($required as $field) {
      if (empty($this->payload[$field])) {
        return false;
      }
    }

    return true;
  }

  /**
   * Compares the passed challenge to the one stored in the database
   *
   * @param $user_id: The the user's ID
   * @param $challenge: The passed challenge
   *
   * @return True on success
   * @return False on failure (The challenge is not valid)
   * @return string 'expired' when the challenge has expired
   */
  public function validate_challenge ($user_id, $challenge) {
    // Request the most recent challenge
    // Note: It's possible for their to be more than on in the DB
    $request = "SELECT * FROM password_reset WHERE user_id='$user_id' ORDER BY id DESC";
    $request = $this->conn->query($request);

    if ($request && $request->num_rows > 0) {
      $request = $request->fetch_assoc();
      // Compare the timelapse
      $timestamp = strtotime($request['created']);
      $time_past = intval(date('U')) - $timestamp;

      if ($time_past > $this->lifetime) {
        return 'expired';
      } else if ($request['challenge'] == $challenge) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Sets the new password
   *
   * @param $user_id : The user's ID in the DB
   * @param $password : The new password, unencrypted
   */
  private function set_new_password ($user_id, $password) {
    $password = $this->encrypt($password);
    $sql = "UPDATE users SET password='$password' WHERE id='$user_id'";
    return $this->conn->query($sql);
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
}
