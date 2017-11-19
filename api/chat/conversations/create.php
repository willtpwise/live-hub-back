<?php
/**
 * Creates a new conversation
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;
use Facebook\FacebookRequest;

class CreateConversation extends APIComponent {
  private $user;
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();
    $this->payload = $payload;

    if (empty($this->payload['user'])) {
      $this->response = new Response([
        'body' => 'Invalid request. Missing user field.'
      ]);
      return;
    }

    if (!REQUEST_USER) {
      $this->response = new Response([
        'body' => 'Invalid request. User must be logged in.'
      ]);
      return;
    }

    $conversation = $this->create();

    if ($conversation) {
      $this->response = new Response([
        'body' => $conversation,
        'token' => create_token(REQUEST_USER)
      ]);
    }
  }

  /**
   * Creates the conversation
   *
   * @return A conversation array
   *
   * @return False
   */
  private function create () {
    $response = [
      'id' => false,
      'members' => [],
      'messages' => []
    ];

    // Create the conversation
    $sql = "INSERT INTO conversations (created) VALUES (" . date('U') . ")";
    $sql = $this->conn->query($sql);

    if ($sql === true) {
      // Store the conversation ID
      $response['id'] = $this->conn->insert_id;

      // Add each member to the conversation
      $members = [REQUEST_USER, $this->payload['user']];
      foreach ($members as $member) {

        // Query the user
        $user_query = "SELECT first_name, last_name FROM users WHERE id = $member";
        $user_query = $this->conn->query($user_query);

        // Query the user's profile image
        $upload_query = "SELECT url FROM uploads WHERE user_id = $member AND name = '48x48'";
        $upload_query = $this->conn->query($upload_query);

        if ($user_query->num_rows > 0) {
          $user = $user_query->fetch_assoc();

          // Extract the image URL
          $upload = '';
          if ($upload_query->num_rows > 0) {
            $upload = $upload_query->fetch_assoc();
          }

          // Add the member to the conversation
          $sql = "INSERT INTO users_in_conversations (user, conversation) VALUES ($member, " . $response['id'] . ")";
          $sql = $this->conn->query($sql);

          // Store the member in the response
          $response['members'][$member] = [
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'image' => $upload
          ];

        } else {
          $this->response = new Response([
            'body' => "Internal error. Unknown user ID '$member'",
            'token' => create_token(REQUEST_USER)
          ]);
          return false;
        }
      }
      return $response;
    } else {
      return false;
    }
  }
}
