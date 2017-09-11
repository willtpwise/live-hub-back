<?php
/**
 * Opens a connection with the database
 *
 * @return object : The database connection
 */
chdir(dirname(__DIR__));
require_once('vendor/autoload.php');

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;

function connect () {
  $config = Factory::fromFile('config/config.php', true);

  $conn = new mysqli(
    $config->get('database')->get('host'),
    $config->get('database')->get('user'),
    $config->get('database')->get('password'),
    $config->get('database')->get('name')
  );

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  } else {
    return $conn;
  }
}
