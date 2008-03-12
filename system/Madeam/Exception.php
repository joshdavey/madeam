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
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Madeam_Exception extends Exception {
  
  const ERR_VIEW_MISSING        = 100;
  const ERR_CONTORLLER_MISSING  = 101;
  const ERR_ACTION_MISSING      = 102;
  const ERR_CLASS_MISSING       = 103;
  const ERR_FILE_MISSING        = 104;
  const ERR_METHOD_MISSING      = 105;  
   
  public function __construct($message, $code = 0) {
    $date = date('M d o H:i:s');
    $file = basename($this->getFile());
    $line = $this->getLine();
    $exception = substr(get_class($this), 17);
    $data = json_encode($_GET);
    
    $fmessage = sprintf("%1$.20s | %2$-28s | %3$-4s | %4$-10s | %5$0s | %6$0s", $date, $file, $line, $exception, $message, $data);
    
    parent::__construct($message, $code);
  }
  
  public function setMessage($message) {
    $this->message = $message;
  }
  
  public function setLine($line) {
    $this->line = $line;
  }
  
  public function setFile($file) {
    $this->file = $file;
  }

}
?>