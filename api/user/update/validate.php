<?php
/**
 * Validates and sanitizes the incoming post data and returns a payload for the db
 *
 * @param array : The post fields to validate
 *
 * @return array : The payload for the db
 * @return false : Indicates the data is invalid
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;

function validate ($fields) {

  $payload = array();

  $required = array(
   'id',
   'username',
   'first_name'
  );

  // Check that all required fields exist
  foreach ($required as $field) {
    if (empty($_POST[$field])) {
      return false;
    }
  }

  // Sanitize and store the payload
  foreach ($fields as $field) {
    $payload[$field] = filter_input(INPUT_POST, $field, FILTER_SANITIZE_STRING);
  }

  // Hash the password
  if (isset($payload['password'])) {
    if (empty($payload['password'])) {
      unset($payload['password']);
    } else {
      $payload['password'] = password_hash($payload['password'], PASSWORD_DEFAULT);
    }
  }
  return $payload;
}
