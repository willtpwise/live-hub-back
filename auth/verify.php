<?php
/**
  * WIP
 * Verifies the authenticity of the current request and revalidates the token
 *
 * @return Success: The JWT token, decoded
 *
 * @return Failure: A Response object with an error message in the body
 */

chdir(dirname(__DIR__));

use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

function verify_token () {
  /*
   * Get all headers from the HTTP request
   */
  $request = new Request();

  // Retrieve the user's JWT
  $jwt = get_token();

  if ($jwt) {
    try {
      /*
       * decode the jwt using the key from config
       */
      $config = Factory::fromFile('config/config.php', true);
      $secretKey = base64_decode($config->get('jwt')->get('key'));
      $token = JWT::decode($jwt, $secretKey, [$config->get('jwt')->get('algorithm')]);

      return $token;

    } catch (Exception $e) {
      /*
       * the token was not able to be decoded.
       * this is likely because the signature was not able to be verified (tampered token)
       */
      return new Response(array(
        'body' => 'Invalid JWT token',
        'header' => 401
      ));
    }
  } else {
      /*
       * No token was able to be extracted from the authorization header
       */
      return new Response(array(
        'body' => 'JWT missing within authorisation header',
        'header' => 400
      ));
  }

  // if ($request->isGet()) {
  //     $authHeader = $request->getHeader('authorization');
  //
  //     /*
  //      * Look for the 'authorization' header
  //      */
  //     if ($authHeader) {
  //         /*
  //          * Extract the jwt from the Bearer
  //          */
  //         list($jwt) = sscanf( $authHeader->toString(), 'Authorization: Bearer %s');
  //
  //         if ($jwt) {
  //             try {
  //                 $config = Factory::fromFile('config/config.php', true);
  //
  //                 /*
  //                  * decode the jwt using the key from config
  //                  */
  //                 $secretKey = base64_decode($config->get('jwt')->get('key'));
  //
  //                 $token = JWT::decode($jwt, $secretKey, [$config->get('jwt')->get('algorithm')]);
  //
  //                 $asset = base64_encode(file_get_contents('http://lorempixel.com/200/300/cats/'));
  //
  //                 /**
  //                  * Valid request
  //                  */
  //                 return json_encode(array(
  //                  'status' => true,
  //                  'jwt' => token($token->data->userId, $token->data->userName)
  //                 ));
  //
  //             } catch (Exception $e) {
  //                 /*
  //                  * the token was not able to be decoded.
  //                  * this is likely because the signature was not able to be verified (tampered token)
  //                  */
  //                 return array(
  //                   'status' => false,
  //                   'error' => 'HTTP/1.0 401 Unauthorized'
  //                 );
  //             }
  //         } else {
  //             /*
  //              * No token was able to be extracted from the authorization header
  //              */
  //             return array(
  //               'status' => false,
  //               'error' => 'HTTP/1.0 400 Bad Request'
  //             );
  //         }
  //     } else {
  //         /*
  //          * The request lacks the authorization token
  //          */
  //         return array(
  //           'status' => false,
  //           'error' => 'HTTP/1.0 400 Bad Request'
  //         );
  //     }
  // } else {
  //     return array(
  //       'status' => false,
  //       'error' => 'HTTP/1.0 405 Method Not Allowed'
  //     );
  // }
}
