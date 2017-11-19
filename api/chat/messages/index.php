<?php
/**
 * Queries for messages within the passed conversation
 *
 * @param $payload: array
 *  - conversation: The conversation ID
 *  - after: A message ID. If passed, only messages that are newer than this ID will be included
 *  - limit: A limit for the response.
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;

class GetMessages extends APIComponent {
  private $user;
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    if (!REQUEST_USER) {
      $this->response = new Response([
        'body' => "Invalid request. User must be logged in.",
        'token' => create_token(REQUEST_USER)
      ]);
      return;
    }

    if (!empty($this->payload['after']) && !is_numeric($this->payload['after'])) {
      $this->response = new Response([
        'body' => 'Invalid request. After option must be numeric.',
        'token' => create_token(REQUEST_USER)
      ]);
      return;
    }

    if (!empty($this->payload['limit']) && !is_numeric($this->payload['limit'])) {
      $this->response = new Response([
        'body' => 'Invalid request. Limit option must be numeric.',
        'token' => create_token(REQUEST_USER)
      ]);
      return;
    }

    if (empty($this->payload['conversation'])) {
      $this->response = new Response([
        'body' => 'Invalid request. Conversation option must be supplied.',
        'token' => create_token(REQUEST_USER)
      ]);
      return;
    }

    if (!is_numeric($this->payload['conversation'])) {
      $this->response = new Response([
        'body' => 'Invalid request. Conversation option must be numeric.',
        'token' => create_token(REQUEST_USER)
      ]);
      return;
    }

    $messages = $this->query();

    if ($messages) {
      $this->response = new Response([
        'body' => $messages,
        'token' => create_token(REQUEST_USER)
      ]);
    }
  }

  /**
   * Retrieves the messages
   *
   * @return An array of messages
   *
   * @return False (error)
   */
  private function query () {

    // Check that the user is in this conversation
    $conversation = "SELECT * FROM users_in_conversations WHERE user = " . REQUEST_USER . " AND conversation = " . $this->payload['conversation'];
    $conversation = $this->conn->query($conversation);

    if ($conversation->num_rows > 0) {

      // Query the messages
      $messages = "SELECT * FROM messages WHERE conversation = " . $this->payload['conversation'];
      if (isset($this->payload['after'])) {
        $messages .= " AND id > " . $this->payload['after'];
      }
      if (isset($this->payload['limit'])) {
        $messages .= " LIMIT " . $this->payload['limit'];
      }
      $messages = $this->conn->query($messages);

      $result = [];
      while ($message = $messages->fetch_assoc()) {
        $result[] = [
          'id' => $message['id'],
          'user' => $message['user'],
          'content' => $message['content'],
          'created' => $message['created']
        ];
      }

      return $result;

    } else {
      $this->response = new Response([
        'body' => "Error. User is not in conversation.",
        'token' => create_token(REQUEST_USER)
      ]);
      return false;
    }

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
