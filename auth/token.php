<?php
/**
 * Returns a JWT token
 *
 * The token has a week-long expiry and it's data claim contains the user's ID and username
 *
 * @param $user_id : int : The user's ID number in the DB
 * @param $user_name : string : The user's username in the DB
 *
 * @return string : A signed JWT
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

function token ($user_id, $user_name) {
  $config     = Factory::fromFile('config/config.php', true);
  $tokenId    = base64_encode(random_bytes(32));
  $issuedAt   = time();
  $notBefore  = $issuedAt;
  $expire     = $notBefore + 604800; // Adding 1 week
  $serverName = $config->get('serverName');

  /**
   * Create the token as an array
   */
  $data = [
      'iat'  => $issuedAt,          // Issued at: time when the token was generated
      'jti'  => $tokenId,           // Json Token Id: an unique identifier for the token
      'iss'  => $serverName,        // Issuer
      'nbf'  => $notBefore,         // Not before
      'exp'  => $expire,            // Expire
      'data' => [                   // Data related to the signer user
          'userId'   => $user_id,   // userid from the users table
          'userName' => $user_name, // User name
      ]
  ];

  /**
   * Extract the key, which is coming from the config file.
   *
   * Best suggestion is the key to be a binary string and
   * store it in encoded in a config file.
   *
   * Can be generated with base64_encode(openssl_random_pseudo_bytes(64));
   *
   * keep it secure! You'll need the exact key to verify the
   * token later.
   */
  $secretKey = base64_decode($config->get('jwt')->get('key'));

  /**
   * Extract the algorithm from the config file too
   */
  $algorithm = $config->get('jwt')->get('algorithm');

  /**
   * Encode the array to a JWT string.
   * Second parameter is the key to encode the token.
   *
   * The output string can be validated at http://jwt.io/
   */
  $jwt = JWT::encode(
      $data,      // Data to be encoded in the JWT
      $secretKey, // The signing key
      $algorithm  // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
      );

  return json_encode($jwt);
}
