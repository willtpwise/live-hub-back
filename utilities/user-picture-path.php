<?php
/**
 * Generates a file path for a user's profile picture
 *
 * `uploads/user/<timestamp>-<filename>`
 *
 * @param $file: The filename, including its extension
 *
 * @return The file path
 */

function user_picture_path ($file) {
  $target_dir = 'uploads/user/';
  $target_name = basename($file);
  $target_name = date('U') . '-' . $target_name;
  $target_name = urlencode(strtolower($target_name));
  $target_name = $target_dir . $target_name;

  return $target_name;
}
