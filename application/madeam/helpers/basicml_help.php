<?php
vendor('basicml/basicml.php');
class basicmlHelp {
  public static function process($str) {
    $basicml = new basicml();
    return $basicml->process($str);
  }
}
?>