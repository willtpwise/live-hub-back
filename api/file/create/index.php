<?php
/**
 * Uploads an image file
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

require_once (__DIR__ . '/make-thumb.php');

class CreateFile extends APIComponent {
  public $payload;
  function __construct ($payload) {
    $this->payload = @$_FILES['upload'];

    if ($this->validate()) {
      $thumbnail_path = user_picture_path($this->payload['tmp_name']);
      $thumbnail = new MakeThumb($this->payload['tmp_name'], $thumbnail_path);
      $thumbnail = $thumbnail->thumbnail();

      if ($thumbnail) {
        $this->response = new Response(array(
          'body' => $thumbnail
        ));
      } else {
        $this->response = new Response(array(
          'header' => 500
        ));
      }
    } else {
      $this->response = new Response(array(
        'body' => 'Invalid request'
      ));
    }
  }

  private function validate () {
    // Check to see the user is logged in
    if (!REQUEST_USER) {
      return false;
    }

    // Check that the file is an image and exists
    if (!isset($this->payload['tmp_name']) || !getimagesize($this->payload['tmp_name'])) {
      return false;
    }

    return true;
  }
}
