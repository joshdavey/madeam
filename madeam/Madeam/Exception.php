<?php
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

}
?>