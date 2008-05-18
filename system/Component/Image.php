<?php

/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Component_Image extends Madeam_Component {

  var $mime_map = array('image/gif' => array('createfrom' => 'imagecreatefromgif', 'image' => 'imagegif'), 'image/jpeg' => array('createfrom' => 'imagecreatefromjpeg', 'image' => 'imagejpeg'), 'image/png' => array('createfrom' => 'imagecreatefrompng', 'image' => 'imagepng'));

  function newsize($image, $toWidth, $toHeight, $favor = 'width') {
    list($width, $height) = getimagesize(PATH_TO_PUBLIC . $image);
    $xscale = $width / $toWidth;
    $yscale = $height / $toHeight;
    if ($yscale > $xscale) {
      $new_width = round($width * (1 / $yscale));
      $new_height = round($height * (1 / $yscale));
    } else {
      $new_width = round($width * (1 / $xscale));
      $new_height = round($height * (1 / $xscale));
    }
    return array('width' => $new_width, 'height' => $new_height);
  }

  /**
   * Re-sizes an image.
   * Requires GD2 library
   *
   * @param unknown_type $image
   * @param unknown_type $toWidth
   * @param unknown_type $toHeight
   * @param unknown_type $favor
   */
  function resize($image, $toWidth, $toHeight, $favor = 'width') {
    $file = getimagesize($image);
    $mime = $file['mime'];
    if (in_array($mime, array_keys($this->mime_map))) {
      $func_createfrom = $this->mime_map[$mime]['createfrom'];
      $func_image = $this->mime_map[$mime]['image'];
      $dimensions = $this->newsize($image, $toWidth, $toHeight, $favor);
      $img = $func_createfrom($image);
      $width = imagesx($img);
      $height = imagesy($img);
      // create a new temporary image
      $tmp_img = imagecreatetruecolor($dimensions['width'], $dimensions['height']);
      // copy and resize old image into new image
      imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $dimensions['width'], $dimensions['height'], $width, $height);
      // create jpeg
      $func_image($tmp_img, PUBLIC_PATH . $image);
      return true;
    } else {
      return false;
    }
  }
}
?>