<?php
class imageHelp extends htmlHelp {
	
	public static function resize($image, $toWidth, $toHeight, $favor = 'width') {  
      
    list($width, $height) = getimagesize(PUB_PATH . $image);
    
    $xscale = $width/$toWidth;
    $yscale = $height/$toHeight;
    
    if ($yscale>$xscale) {
      $new_width  = round($width * (1/$yscale));
      $new_height = round($height * (1/$yscale));
    } else {
      $new_width  = round($width * (1/$xscale));
      $new_height = round($height * (1/$xscale));
    }
    
    return array('width' => $new_width, 'height' => $new_height);
  }
	
}
?>