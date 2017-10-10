<?php
/**
 * JSON Web Tokens
 *
 * Utilities for creating, using and verifying JWTs
 */
chdir(dirname(__DIR__));

use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

/**
 * Decodes a JWT into an array
 *
 * @param $token: The JWT token
 *
 * @return Success: The JWT decoded into an array
 *
 * @return Failure: null, if the JWT was empty
 *
 * If the JWT is invalid (I.e. it's been tampered with) this function will fail
 * and cause PHP to die.
 */
function decode_token ($token) {
  if ($token) {
    $config = Factory::fromFile('config/config.php', true);
    $secretKey = base64_decode($config->get('jwt')->get('key'));
    try {
      $token = JWT::decode($token, $secretKey, [$config->get('jwt')->get('algorithm')]);
    } catch (Exception $e) {
      return false;
    }
    return $token;
  }
}

/**
 * Retrieves the current user's JWT from the 'authorization' header
 *
 * @return Success: string : The JWT, unmodified
 *
 * @return Failure: bool : false (The JWT doesn't exist)
 */
function get_token () {
  $request = new Request();
  $authHeader = $request->getHeader('authorization');
  if (!$authHeader) {
    return false;
  }

  list($jwt) = sscanf($authHeader->toString(), 'Authorization: Bearer %s');
  if ($jwt) {
    return $jwt;
  } else {
    return false;
  }
}

/**
 * Creates a JWT token
 *
 * The token has a week-long expiry and it's data claim contains the user's ID
 *
 * @param $user_id : int : The user's ID number in the database
 *
 * @return string : A signed JWT
 */
function create_token ($user_id) {
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
          'userId'  => $user_id     // userid from the users table
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

  return $jwt;
}
