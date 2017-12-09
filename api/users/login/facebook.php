<?php
/**
 * Validates a facebook auth token and exposes the user's FB ID
 */

use Firebase\JWT\JWT;
use Zend\Config\Factory;
use GeoIp2\Database\Reader;
use Zend\Http\PhpEnvironment\Request;

class FacebookLogin extends APIComponent {
  public $user;
  public $token;
  private $fb;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();

    try {
      $this->token = new Facebook\Authentication\AccessToken($payload['facebook_token']);
    } catch (Exception $e) {
      $this->response = new Response([
        'header' => 500,
        'body' => 'Internal error: ' . $e
      ]);
    }

    $credentials = $this->facebook_credentials();

    $this->fb = new \Facebook\Facebook([
      'app_id' => $credentials['id'],
      'app_secret' => $credentials['secret'],
      'default_graph_version' => 'v2.10'
    ]);

    $this->authenticate();
  }

  public function authenticate () {
    try {
      $response = $this->fb->get('/me', $this->token);
    } catch(\Facebook\Exceptions\FacebookResponseException $e) {
      $this->status = false;
      return;
    } catch(\Facebook\Exceptions\FacebookSDKException $e) {
      $this->status = false;
      return;
    }

    $user = $response->getGraphUser();
    $id = $user->getId();

    $id = $this->conn->real_escape_string($id);
    $sql = "SELECT id FROM users WHERE facebook_id='$id'";
    $sql = $this->conn->query($sql);

    if ($sql && $sql->num_rows > 0) {
      $result = $sql->fetch_assoc();
      $this->user = ['id' => $result['id']];
      $this->status = true;
    } else {
      $this->status = false;
    }
  }

  public function facebook_credentials () {
    $credentials = [
      'livehub.com.au' => [
        'id' => '1574441412599266',
        'secret' => '6961b76cf6bdc1a86f6555eebfcd30b5'
      ],
      'livehub-staging.net' => [
        'id' => '1993645014228196',
        'secret' => '741cb3c916c209485528bf3dcb13a32d'
      ],
      'live-hub-back.int' => [
        'id' => '804109516440144',
        'secret' => 'bd68cd6638996978d23fca7c89a531f9'
      ]
    ];

    foreach (array_keys($credentials) as $domain) {
      if (strpos($_SERVER['HTTP_REFERER'], $domain) !== false) {
        return $credentials[$domain];
      }
    }

    return $credentials['live-hub-back.int'];
  }
}
