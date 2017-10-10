<?php
/**
 * Uploads an image file
 */

use Zend\Config\Factory;
use Zend\Http\PhpEnvironment\Request;
use Firebase\JWT\JWT;
use Aws\S3\S3Client;

require_once (__DIR__ . '/thumbnail.php');

class CreateFile extends APIComponent {
  public $payload;
  public $processes;
  public $conn;
  function __construct ($payload) {
    $this->conn = connect();

    // Payload defaults
    // Defaults are used for post requests
    $this->payload = array_merge([
      'user_id' => REQUEST_USER,
      'file' => @$_FILES['upload']['tmp_name'],
      'overide' => true,
      'taxonomy' => 'display'
    ], $payload);


    // Image set sizes by taxonomy
    $this->processes = [
      'display' => [256, 48, 600]
    ];

    // Validate the incoming request
    if (!$this->validate()) {
      $this->response = new Response(array(
        'body' => 'Invalid request'
      ));
      return;
    }

    // Records the processed file paths
    $processed_files = [];

    // Set a common filename for the processed files
    $common_filename = 'uploads/temp/' . date('U') . '-%s.jpg';

    // Convert the file to jpeg
    $master = $this->convert_to_jpg($this->payload['file']);

    // Save a local copy of the file
    $save_to = sprintf($common_filename, 'master');
    $this->create_temp_file($master, $save_to);
    $master = $save_to;
    $processed_files['master'] = $master;

    // Crop the upload into it's desired size(s)
    $save_sizes = $this->processes[$this->payload['taxonomy']];
    foreach ($save_sizes as $size) {
      $save_to = sprintf($common_filename, $size);
      $this->crop($master, $size, $save_to);
      $processed_files[$size] = $save_to;
    }

    // Dump old results
    if ($this->payload['overide']) {
      $dumped_objects = $this->dump_from_db($this->payload['taxonomy']);
      foreach ($dumped_objects as $object) {
        $this->dump_from_s3($object);
      }
    }

    // Save each file to S3
    foreach (array_keys($processed_files) as $size) {
      $remote_url = $this->save_to_s3($processed_files[$size]);
      if ($remote_url) {
        $processed_files[$size] = [
          'local' => $processed_files[$size],
          'remote' => $remote_url
        ];
      } else {
        $this->response = new Response([
          'header' => 500,
          'body' => 'Failed to connect to S3'
        ]);
        return;
      }
    }

    // Save each file to the db
    foreach (array_keys($processed_files) as $size) {
      $processed_files[$size]['db'] = $this->save_to_db(
          $processed_files[$size]['local'],
          $processed_files[$size]['remote'],
          $this->payload['taxonomy'],
          $size === 'master' ? $size : false
      );
    }

    // Dump the local copies
    foreach ($processed_files as $file) {
      $this->dump_temp_file($file['local']);
    }

    // Response
    $request = new GetFile([
      'taxonomy' => 'display'
    ]);
    $this->response = $request->response;

  }

  /**
   * Takes the passed file reference, converts it to jpeg and returns an image
   * stream
   *
   * @param $file The absolute path to the file
   *
   * @return An image stream
   * @return false if the image is not a jpg, png or gif
   */
  public function convert_to_jpg ($file) {
    // Get mime type
    $mime = mime_content_type($file);

    // Convert
    switch ($mime) {
      case 'image/jpeg':
      case 'image/jpg':
        return imagecreatefromjpeg($file);
      case 'image/png':
        return imagecreatefrompng($file);
      case 'image/gif':
        return imagecreatefromgif($file);
    }

    return false;
  }

  /**
   * Create's a local disk copy of the passed file reference
   *
   * @param $stream: An image stream. Must be a jpeg
   * @param $save_to: The absolute path to save the file to
   *
   * @return True / False indicating whether the file was saved
   */
  private function create_temp_file ($stream, $save_to) {
    return imagejpeg($stream, $save_to);
  }

  /**
   * Delete's the local file reference
   *
   * @param $filepath: The absolute file path to be deleted
   *
   * @return True / False indicating whether the file was deleted
   */
  private function dump_temp_file ($filepath) {
    return unlink($filepath);
  }

  /**
   * Crops the passed image reference to the desired square size. Enlarging
   * the image if need be. The original image will not be overwritten
   *
   * @param $original (string) The absolute path to the image. Must be a jpeg
   * @param $size (int) The desired width & height of the image
   * @param $save_to (string) The absolute path to save the image to
   *
   * @return True / False indicating whether the file was saved correctly
   */
  public function crop ($original, $size = 300, $save_to) {
    // Create a copy of the image in memory
    $image = imagecreatefromjpeg($original);

    // Grab the image dimensions, along with the smallest side
    list($width, $height) = getimagesize($original);
    if ($width > $height) {
      $y = 0;
      $x = ($width - $height) / 2;
      $smallest_side = $height;
    } else {
      $x = 0;
      $y = ($height - $width) / 2;
      $smallest_side = $width;
    }

    // Crop
    $processed = imagecreatetruecolor($size, $size);
    imagecopyresampled($processed, $image, 0, 0, $x, $y, $size, $size, $smallest_side, $smallest_side);

    // Save
    return imagejpeg($processed, $save_to);
  }

  /**
   * Checks that the incoming image is a valid file for upload
   *
   * Specifically checks that it is indeed an image and that the user is logged
   * in
   *
   * @return True / False, indicating whether the request should be accepted
   */
  public function validate () {
    // Check to see the user is logged in
    if (REQUEST_URI !== '/users/create/' && !REQUEST_USER) {
      return false;
    }

    // Check that the file is an image and exists
    if (!file_exists($this->payload['file']) || !getimagesize($this->payload['file'])) {
      return false;
    }

    return true;
  }

  /**
   * Save's the passed file path to the DB
   *
   * @param $local: The absolute path to the local file
   * @param $remote: The absolute path to the remote file (s3)
   * @param $taxonomy: The image collection name
   * @param $name: A friendly upload name. Defaults to '(width)x(height)'
   *
   * @return The last insert id
   */
  public function save_to_db($local, $remote, $taxonomy = '', $name = false) {

    list($width, $height) = getimagesize($local);

    if (!$name) {
      $name = sprintf('%sx%s', $width, $height); // E.g. '200x200'
    }

    $payload = [
      'mime' => mime_content_type($local),
      'size' => filesize($local),
      'name' => $name,
      'width' => $width,
      'height' => $height,
      'taxonomy' => $taxonomy,
      'url' => $remote,
      'user_id' => $this->payload['user_id']
    ];

    // Sanitize
    foreach (array_keys($payload) as $key) {
      $payload[$key] = $this->conn->real_escape_string($payload[$key]);
    }

    // Store
    $fields = implode(", ", array_keys($payload));
    $values = "'" . implode("', '", $payload) . "'";
    $sql = "INSERT INTO uploads ($fields) VALUES ($values)";
    $sql = $this->conn->query($sql);

    return $this->conn->insert_id;
  }

  /**
   * Dumps an image set from the DB for the current user (REQUEST_USER)
   *
   * @param $taxonomy: The image set's name, E.g. 'display'
   *
   * @return An array containing the URLs of any deleted results
   */
  public function dump_from_db($taxonomy) {
    $user_id = $this->conn->real_escape_string(REQUEST_USER);
    $taxonomy = $this->conn->real_escape_string($taxonomy);

    // Get
    $sql = "SELECT url FROM uploads WHERE taxonomy='$taxonomy' AND user_id='$user_id'";
    $result = $this->conn->query($sql);

    // Store the URLs
    if ($result && $result->num_rows > 0) {
      $deleted_objects = [];
      while ($row = $result->fetch_assoc()) {
        $deleted_objects[] = $row['url'];
      }
    } else {
      return [];
    }

    // Delete
    $sql = "DELETE FROM uploads WHERE taxonomy='$taxonomy' AND user_id='$user_id'";
    $this->conn->query($sql);

    return $deleted_objects;
  }

  /**
   * Sends the passed file reference to S3
   *
   * @param $filepath: The absolute file path (disk)
   *
   * @return An absolute file path on S3
   * @return False on error
   */
  public function save_to_s3 ($filepath) {
    $config = Factory::fromFile('config/config.php', true);
    $bucket = 'live-hub-uploads';
    $keyname = explode('/', $filepath);
    $keyname = $keyname[count($keyname) - 1];

    try {

      // Instantiate the client.
      $s3 = S3Client::factory([
        'region'  => 'ap-southeast-2',
        'version' => '2006-03-01',
        'credentials' => [
          'key' => $config->s3->key,
          'secret'  => $config->s3->secret
        ]
      ]);

      // Upload a file.
      $result = $s3->putObject(array(
          'Bucket'       => $bucket,
          'Key'          => $keyname,
          'SourceFile'   => $filepath,
          'ContentType'  => mime_content_type($filepath),
          'ACL'          => 'public-read',
          'StorageClass' => 'REDUCED_REDUNDANCY'
      ));

      return $result['ObjectURL'];
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * Dumps the passed file reference from S3
   *
   * @param $filepath: The absolute file path to the remote file
   *
   * @return True / False
   */
  public function dump_from_s3 ($filepath) {
    $config = Factory::fromFile('config/config.php', true);
    $bucket = 'live-hub-uploads';
    $keyname = explode('/', $filepath);
    $keyname = $keyname[count($keyname) - 1];

    try {
      // Instantiate the client.
      $s3 = S3Client::factory([
        'region'  => 'ap-southeast-2',
        'version' => '2006-03-01',
        'credentials' => [
          'key' => $config->s3->key,
          'secret'  => $config->s3->secret
        ]
      ]);

      // Delete the file.
      return $s3->deleteObject([
        'Bucket' => $bucket,
        'Key'    => $keyname
      ]);

    } catch (Exception $e) {
      return false;
    }
  }
}
