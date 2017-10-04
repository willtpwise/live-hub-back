<?php
class Thumbnail {
  public $src;
  public $save;
  function __construct ($src, $save) {
    $this->src = $src;
    $this->save = $save;
    $this->save_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $save;
  }

  public function thumbnail () {
    // Grab the file extension
    $ext = mime_content_type($this->src);

    // Store the image to the local memory
    if ($ext === 'image/jpeg' || $ext === 'image/jpg') {
      $image = imagecreatefromjpeg($this->src);
    } else if ($ext === 'image/png') {
      $image = imagecreatefrompng($this->src);
    } else if ($ext === 'image/gif') {
      $image = imagecreatefromgif($this->src);
    } else {
      // Invalid file format
      return false;
    }

    // Grab the image dimensions
    list($width, $height) = getimagesize($this->src);
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
    $thumb_size = 400;
    $thumb = imagecreatetruecolor($thumb_size, $thumb_size);
    imagecopyresampled($thumb, $image, 0, 0, $x, $y, $thumb_size, $thumb_size, $smallest_side, $smallest_side);

    // Save
    // Store the image to the local memory
    if ($ext === 'image/jpeg' || $ext === 'image/jpg') {
      imagejpeg($thumb, $this->save_path);
    } else if ($ext === 'image/png') {
      imagepng($thumb, $this->save_path);
    } else if ($ext === 'image/gif') {
      imagegif($thumb, $this->save_path);
    }

    return $this->save;
  }
}
