<?php
/**
 * Lists a single user
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class GetUser extends APIComponent {
  private $query;

  function __construct ($query) {
    $this->query = $query;

    if (!isset($this->query['id'])) {
      // This requests appears to be for the logged in user
      // Validate and decode their token to attain a user id
      $token = get_token();
      $token = decode_token($token);
      if ($token) {
        $this->query['id'] = $token->data->userId;
      }
    }

    $this->lookup($this->query['id']);
  }

  private function lookup ($user_id) {
    $conn = connect();

    // Profile info
    $profile = "SELECT * FROM users WHERE id = '$user_id'";
    $profile = $conn->query($profile);

    // Meta info
    $meta = "SELECT * FROM meta WHERE user_id = '$user_id'";
    $meta = $conn->query($meta);

    if ($profile && $meta) {

      $profile = $profile->fetch_assoc();
      $meta = $meta->fetch_assoc();

      // Pre-format the returned result
      $user = array(
        'instruments' => array(),
        'contact' => array(),
        'social' => array()
      );

      // Format the profile data
      if ($profile) {
        $user = array_merge($user, $profile);
        unset($user['password']);
      }

      // Format the meta data
      if ($meta) {
        foreach ($meta as $item) {
          $user[$item['category']] = array(
            'type' => $item['type'],
            'val' => $item['val']
          );
        }
      }

      $this->response = new Response(array(
        'body' => $user
      ));

    } else {

      $this->response =  new Response(array(
        'header' => 404
      ));

    }

  }
}
