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

    if ($this->validate()) {
      $this->dump_details();
      $this->save();
    }
  }

  private function save ($user_id) {
    $conn = connect();

    // Basic profile info
    $profile = "SELECT * FROM users WHERE id = '$user_id'";
    $profile = $conn->query($profile);

    // Details info
    $meta = "SELECT * FROM details WHERE user_id = '$user_id'";
    $meta = $conn->query($meta);

    if ($profile && $details) {

      $profile = $profile->fetch_assoc();
      $details = $details->fetch_assoc();

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

      // Format the details data
      if ($details) {
        foreach ($details as $detail) {
          $user[$detail['category']] = array(
            'type' => $detail['type'],
            'val' => $detail['val']
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
