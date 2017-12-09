<?php
/**
 * Sends an email notification
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;
use Aws\Common\Aws;
use Aws\Ses\SesClient;

class Notify extends APIComponent {
  private $payload;
  private $conn;

  function __construct ($payload) {
    $this->conn = connect();

    // Payload
    // Defaults are used for post requests
    $this->payload = array_merge([
      'id' => REQUEST_USER,
      'to' => false,
      'name' => 'welcome',
      'data' => []
    ], $payload);

    // Get the user's details
    if ($this->payload['to']) {
      $args = ['email' => $this->payload['to']];
    } else {
      $args = ['id' => $this->payload['id']];
    }
    $user = new GetUsers($args);
    if ($user->response->get('body') && $user->response->get('body')[0]) {
      $this->user = $user->response->get('body')[0];
    } else {
      // The user does not exist
      $this->response = new Response([
        'body' => 'Invalid request. Unknown to address.'
      ]);
      return;
    }

    // Get the message body
    $template = $this->get_template($this->payload['template'], $this->payload['data']);
    if (!$template) {
      $this->response = new Response([
        'header' => 500,
        'body' => 'Internal error. Unknown template'
      ]);
      return;
    }

    // Send the email
    $this->response = new Response([
      'body' => 'Send: ' . $this->send(
        $template['subject'],
        $template['content'],
        $this->payload['to']
      )
    ]);
  }

  /**
   * Returns the processed HTML for an email
   *
   * @param string $name: The name of the template
   *
   * @return The processed HTML if the template exists
   * @return False if the template does not exist
   */
  private function get_template($name, $data) {
    $templates = [
      'welcome' => [
        'subject' => 'Welcome to LiveHUB',
        'content' => 'welcome.php'
      ],
      'password_reset' => [
        'subject' => 'Password reset',
        'content' => 'password_reset.php'
      ]
    ];
    if (isset($templates[$name])) {
      $path = __DIR__ . "/templates/" . $templates[$name]['content'];
      if (file_exists($path)) {
        include $path;
        $templates[$name]['content'] = call_user_func(
          $name . '_template',
          $this->user,
          $this->payload['data']
        );
        return $templates[$name];
      }
    }
  }

  /**
   * Sends the email via AWS SES
   *
   * @param string $subject: The subject of the email
   * @param string $message: The HTML message
   * @param string $to: The email address to send to
   *
   * @return True / False indicating whether the email was sent
   */
  private function send ($subject, $message, $to) {
    $config = Factory::fromFile('config/config.php', true);
    $client = SesClient::factory(array(
        // 'profile' => 'personal',
        'region'  => 'us-east-1',
        'version' => 'latest',
        'credentials' => [
          'key' => $config->s3->key,
          'secret'  => $config->s3->secret
        ]
    ));

    $response = $client->sendEmail(array(
      // Source is required
      'Source' => 'noreply@livehub.com.au',
      // Destination is required
      'Destination' => array(
        'ToAddresses' => array($to)
      ),
      // Message is required
      'Message' => array(
        // Subject is required
        'Subject' => array(
          // Data is required
          'Data' => $subject,
        ),
        // Body is required
        'Body' => array(
          'Html' => array(
            // Data is required
            'Data' => $message
          ),
        ),
      )
    ));
  }
}
