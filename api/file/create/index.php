<?php
/**
 * Uploads an image file
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class CreateFile extends APIComponent {
  public $payload;

  function __construct ($payload) {
    $this->payload = @$_FILES['upload'];

    if ($this->validate()) {
      $this->response = $this->save();
    } else {
      $this->response = new Response(array(
        'header' => 400
      ));
    }
  }

  private function save () {
    // TO DO: Save the file under the user's ID
    // Check the the user is logged in and validate the JWT
    $target_dir = 'uploads/';
    $target_file = $target_dir . basename($this->payload["name"]);

    if (move_uploaded_file($this->payload['tmp_name'], $target_file)) {
      return new Response(array(
        'body' => $target_file
        // TO DO: Send JWT token
      ));
    } else {
      return new Response(array(
        'header' => 500
      ));
    }
  }

  private function validate () {
    if (!$this->payload) {
      return false;
    }

    // Check that the file is an image and exists
    $size = getimagesize($this->payload['tmp_name']);
    if (!$size) {
      return false;
    }

    return true;
  }
}
