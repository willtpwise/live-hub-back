<?php
/**
 * Defines global information on the incoming request
 */

(function () {
  // The original request URI
  $request_uri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);

  // The user's JWT token
  $token = get_token();
  $decoded_token = decode_token($token);

  // The user's ID
  $user_id = false;
  if ($decoded_token) {
    $user_id = $decoded_token->data->userId;
  }

  define ('REQUEST_URI', $request_uri);
  define ('REQUEST_TOKEN', $token);
  define ('REQUEST_USER', $user_id);
})();
