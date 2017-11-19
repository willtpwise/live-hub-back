<?php
/**
 * Creates a new message
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;
use Facebook\FacebookRequest;

class CreateMessage extends APIComponent {
  private $user;
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    if (!REQUEST_USER) {
      $this->response = new Response([
        'body' => 'Invalid request. User must be logged in.'
      ]);
      return;
    }
    if (empty($this->payload['content'])) {
      $this->response = new Response([
        'body' => 'Invalid request. Missing content field.'
      ]);
      return;
    }

    if (empty($this->payload['conversation'])) {
      $this->response = new Response([
        'body' => 'Invalid request. Missing conversation field.'
      ]);
      return;
    }

    $this->payload['content'] = $this->conn->real_escape_string($this->payload['content']);
    $this->payload['conversation'] = $this->conn->real_escape_string($this->payload['conversation']);

    $message = $this->create();

    if ($message) {
      $this->response = new Response([
        'body' => $message,
        'token' => create_token(REQUEST_USER)
      ]);
    } else {
      $this->response = new Response([
        'body' => 'Internal error',
        'token' => create_token(REQUEST_USER)
      ]);
    }
  }

  /**
   * Creates the message
   *
   * @return A message array
   *
   * @return False (error)
   */
  private function create () {

    // Prepare the payload
    $payload = [
      'user' => REQUEST_USER,
      'conversation' => $this->payload['conversation'],
      'content' => $this->payload['content'],
      'created' => date('U')
    ];

    // Create the conversation
    $fields = implode(", ", array_keys($payload));
    $values = "'" . implode("', '", $payload) . "'";

    $sql = "INSERT INTO messages ($fields) VALUES ($values)";
    $sql = $this->conn->query($sql);

    if ($sql === true) {
      return [
        'id' => $this->conn->insert_id,
        'user' => REQUEST_USER,
        'content' => $payload['content'],
        'timestamp' => $payload['created']
      ];
    } else {
      return false;
    }

  }
}
