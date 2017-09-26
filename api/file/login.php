<?php
/**
 * Attempts to log a user in by checking their passed username and password
 * against records stored in the DB. Data should be sent via HTTPS post
 *
 * Post data names:
 *  - username
 *  - password
 *
 * If the details are valid, a valid JWT will be generated and returned. Otherwise
 * an HTTP error header will be returned.
 */
chdir(dirname(__DIR__));

require_once('vendor/autoload.php');
require_once('auth/token.php');
require_once('database/connect.php');

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

$request = new Request();
/**
 * Validate that the request was made using HTTP POST method
 */
if ($request->isPost()) {
    /**
     * Simple sanitization
     */
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($username && $password) {
        try {
            $conn = connect();
            $sql = "SELECT * FROM users WHERE username = '$username'";
            $result = $conn->query($sql);

            if ($result) {

                $result = $result->fetch_assoc();

                if (password_verify($password, $result['password'])) {

                    header('Content-type: application/json');
                    echo token($result['id'], $username);

                } else {
                    header('HTTP/1.0 401 Unauthorized');
                }
            } else {
                header('HTTP/1.0 401 Unauthorized');
            }
        } catch (Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
        }
    } else {
        header('HTTP/1.0 400 Bad Request');
    }
} else {
    header('HTTP/1.0 405 Method Not Allowed');
}
