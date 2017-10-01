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
      $thumbnail = new MakeThumb($this->payload['tmp_name'], $this->create_name());
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

  private function create_name () {
    // Format: `uploads/user/<user id>/<timestamp>-<filename>`
    $target_dir = 'uploads/user/' . REQUEST_USER . '/';
    if (!file_exists($target_dir)) {
      mkdir($target_dir);
    }
    $target_name = basename($this->payload["name"]);
    $target_name = date('U') . '-' . $target_name;
    $target_name = urlencode(strtolower($target_name));
    $target_name = $target_dir . $target_name;

    return $target_name;
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
