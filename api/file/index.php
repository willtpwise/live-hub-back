<?php
/**
 * Queries the uploads database
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class GetFile extends APIComponent {
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();

    // Payload
    // Defaults are used for post requests
    $this->payload = array_merge([
      'user_id' => REQUEST_USER,
      'taxonomy' => 'display'
    ], $payload);

    // Query
    $results = $this->query($this->payload['user_id'], $this->payload['taxonomy']);
    if ($results) {
      $this->response = new Response([
        'body' => $results
      ]);
    } else {
      $this->response = new Response([
        'body' => []
      ]);
    }
  }

  /**
   * Retrieves the requested files from the DB
   *
   * @param int $user_id: The user's ID in the database
   * @param string $taxonomy: The image collection name. E.g. 'display'
   *
   * @return An array of files found
   * @return An empty, if no files are found
   */
  public function query($user_id, $taxonomy) {
    // Sanitize
    $user_id = $this->conn->real_escape_string(intval($user_id));
    $taxonomy = $this->conn->real_escape_string($taxonomy);

    // Request
    $sql = "SELECT * FROM uploads WHERE user_id='$user_id' AND taxonomy='$taxonomy'";
    $sql = $this->conn->query($sql);

    // Response
    $response = [];
    if ($sql) {
      while ($row = $sql->fetch_assoc()) {
        $response[$row['name']] = $row['url'];
      }
    }
    return $response;
  }
}
