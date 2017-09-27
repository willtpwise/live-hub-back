<?php
/**
 * Uploads an image file
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class CreateFile extends APIComponent {
  public $payload;
  private $user;
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
    $target_dir = 'uploads/user/' . $this->user->userId . '/';
    if (!file_exists($target_dir)) {
      mkdir($target_dir);
    }
    $target_name = date('U') . '-' . basename($this->payload["name"]);
    $target_name = urlencode(strtolower($target_name));
    $target_file = $target_dir . $target_name;

    if (move_uploaded_file($this->payload['tmp_name'], $target_file)) {
      return new Response(array(
        'body' => $target_file,
        'token' => token($this->user->userId, $this->user->userName)
      ));
    } else {
      return new Response(array(
        'header' => 500
      ));
    }
  }

  private function validate () {
    // If this is an HTTP request, verify the user's token
    $request = new Request();
    if ($request->isPost()) {
      $token = get_token();
      if (verify_token($token)) {
        $token = decode_token($token);
        $this->user = $token->data;
      } else {
        return false;
      }
    }

    // Check that the file is an image and exists
    $size = getimagesize($this->payload['tmp_name']);
    if (!$size) {
      return false;
    }

    return true;
  }
}
