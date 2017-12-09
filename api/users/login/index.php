<?php
/**
 * Handles the login process
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;

class LoginUser extends APIComponent {
  private $payload;

  function __construct ($payload) {
    if (isset($payload['facebook_token'])) {
      require_once (__DIR__ . '/facebook.php');
      $login = new FacebookLogin($payload);
    } else {
      require_once (__DIR__ . '/manual.php');
      $login = new ManualLogin($payload);
    }

    if ($login->status) {
      $this->response = new Response([
        'body' => 'success',
        'token' => create_token($login->user['id'])
      ]);
    } else {
      $this->response = new Response([
        'body' => 'Failed to sign in'
      ]);
    }
  }
}
