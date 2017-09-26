<?php
/**
 * The response class provides a single way for API methods to respond to incoming requests
 */

class Response {
  public $args;
  function __construct ($args) {
    $this->args = array_merge(array(
      'token' => null,
      'body' => null,
      'header' => null
    ), $args);
  }

  public function format ($args) {
    return json_encode($args);
  }

  public function send () {
    $headers = array(
      '404' => 'HTTP/1.0 404 Not Found',
      '500' => 'HTTP/1.0 500 Internal Server Error',
      '401' => 'HTTP/1.0 401 Unauthorized',
      '400' => 'HTTP/1.0 400 Bad Request'
    );

    $header_code = intval($this->args['header']);
    if (isset($headers[$header_code])) {
      header($headers[$header_code]);
    }

    echo $this->format($this->args);
  }
} ?>
