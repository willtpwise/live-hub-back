<?php
/**
 * Lists a single user
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;

class GetUsers extends APIComponent {
  private $query;
  private $conn;

  function __construct ($query) {
    $this->conn = connect();
    $this->query = [
      $this->filter_query($query),
      $this->get_order($query),
      $this->get_limit($query)
    ];

    $this->query = implode(' ', $this->query);

    $this->response = new Response([
      'body' => $this->lookup()
    ]);
  }

  /**
   * Returns a SQL Order statement
   *
   * @param $query. The incoming payload. Specifically looks for the 'order' prop
   *
   * @return An empty string, or a SQL Order statement
   */
  private function get_order ($query) {
    if (empty($query['order'])) {
      return '';
    } else {
      $order = $query['order'];
      if (is_array($order['column'])) {
        $order['column'] = implode(',', $order['column']);
      }
      return sprintf('ORDER BY %s %s',
        $this->conn->real_escape_string($order['column']),
        $this->conn->real_escape_string($order['direction'])
      );
    }
  }

  /**
   * Returns a SQL Limit statement
   *
   * @param $query. The incoming payload. Specifically looks for the 'limit' prop
   *
   * @return An SQL limit statement or an empty string if the limit prop is set
   * to '-1'
   *
   * Note: The default value for the limit statment is 25. Pass -1 to return all
   * results
   */
  private function get_limit ($query) {
    if (!isset($query['limit'])) {
      return 'LIMIT 25';
    } else if (intval($query['limit']) < 0) {
      return '';
    } else {
      return 'LIMIT ' . $query['limit'];
    }
  }

  /**
   * Filters the incoming query data to a MySQL WHERE clause
   *
   * @param $query: The query paramaters. If empty, and the user is logged in
   * this will default to the user's ID within their JWT
   *
   * @return A string, suitable for a WHERE clause. (Including the 'WHERE' part)
   * @return If the query was empty an empty string is returned
   */
  private function filter_query ($query) {
    $clause = [];
    // If this request is for the current user, attempt to get their ID from
    // their JWT
    if (@$query['id'] === 'current') {
      $query['id'] = REQUEST_USER ? REQUEST_USER : 0;
    }

    // Convert the query array into logical operators
    $query_keys = array_keys($query);
    $special = ['order', 'limit'];
    foreach ($query_keys as $key) {
      if (!in_array($key, $special)) {
        if (is_array($query[$key])) {
          $condition = $query[$key];
          $clause[] = sprintf("%s %s '%s'",
            $this->conn->real_escape_string($key),
            $this->conn->real_escape_string($condition['operator']),
            $this->conn->real_escape_string($condition['value'])
          );
        } else {
          $val = $query[$key];
          $clause[] = "$key = $val";
        }
      }
    }

    // Combine the query and return the clause
    // If the query was empty, return an empty string
    if (count($clause) > 0) {
      return 'WHERE ' . implode(' AND ', $clause);
    } else {
      return '';
    }
  }

  /**
   * Performs the database lookup
   *
   * @return If there's one or more results, an array containing the results
   * @return If there's no results, an empty array
   */
  private function lookup () {
    $results = [];

    // Query the users
    $users = "SELECT * FROM users " . $this->query;
    $users = $this->conn->query($users);

    if ($users) {
      while ($user = $users->fetch_assoc()) {
        // Query the user's meta data
        $meta = "SELECT * FROM meta WHERE user_id = '" . $user['id'] . "'";
        $meta = $this->conn->query($meta);

        // Format and store the result
        $results[] = $this->format_user($user, $meta);
      }
    } else {
      $this->response = new Response([
        'body' => []
      ]);
    }

    return $results;
  }

  /**
   * Format's the passed profile and meta data into the REST APIs format
   *
   * @param $profile: The user's profile information, from the users table
   * @param $meta: The user's meta data, from the meta table
   *
   * @return The user's data as an array
   */
  private function format_user ($user, $meta) {
    // Ensure the data model is up to spec, by filling in meta fields that may
    // be missing
    $user['meta'] = [
      'contact' => [],
      'instruments' => [],
      'social' => []
    ];

    // Append the meta data to the user by category
    if ($meta->num_rows > 0) {
      while ($meta_item = $meta->fetch_assoc()) {
        $user['meta'][$meta_item['category']][] = [
          'type' => $meta_item['type'],
          'val' => $meta_item['val']
        ];
      }
    }

    return $user;
  }
}
