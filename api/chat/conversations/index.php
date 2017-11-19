<?php
/**
 * Returns the queried conversations for the passed user
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;
use Facebook\FacebookRequest;

class GetConversations extends APIComponent {
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

    $response = [];
    $conversations = $this->query();

    foreach ($conversations as $conversation) {
      $response[] = $this->format($conversation);
    }

    $this->response = new Response([
      'body' => $response,
      'token' => create_token(REQUEST_USER)
    ]);
  }

  /**
   * Queries for conversations
   *
   * @return An array of conversations
   *
   * @return an empty array (Zero results)
   */
  private function query () {

    $conversations = [];

    // Query all conversations
    $sql = "SELECT conversations.id FROM conversations INNER JOIN users_in_conversations ON conversations.id = users_in_conversations.conversation WHERE users_in_conversations.user = " . REQUEST_USER;
    $sql = $this->conn->query($sql);

    // Format the response
    while ($row = $sql->fetch_assoc()) {
      $conversations[] = $row['id'];
    }

    return $conversations;

  }

  /**
   * Formats a single conversation
   */
  private function format ($conversation) {

    $response = [
      'id' => $conversation,
      'members' => [],
      'messages' => []
    ];

    // Query the messages
    $messages = "SELECT * FROM messages WHERE conversation = $conversation";
    $messages = $this->conn->query($messages);

    if ($messages->num_rows > 0) {

      while($message = $messages->fetch_assoc()) {

        // Add the message
        $response['messages'][] = [
          'user' => $message['user'],
          'content' => $message['content'],
          'created' => $message['created']
        ];
      }
    }

    // Query the members
    $members = "SELECT * FROM users_in_conversations WHERE conversation = $conversation";
    $members = $this->conn->query($members);
    while ($member = $members->fetch_assoc()) {
      $response['members'][$member['user']] = $this->member($member['user']);
    }

    return $response;
  }

  private function member ($member) {

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

      return [
        'name' => $user['first_name'] . ' ' . $user['last_name'],
        'image' => $upload['url']
      ];

    } else {

      // Return an anonymous user (This one has been deleted)
      return [
        'name' => 'Unknown user',
        'image' => ''
      ];

    }
  }
}
