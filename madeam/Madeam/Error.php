<?php
class Madeam_Error {
  
  const ERR_CRITICAL  = 0;
  const ERR_NOT_FOUND = 50;
  
  public function catchException(Exception $exception, $code = 100) {
    if (MADEAM_ENABLE_DEBUG === true) {
      exit('<p>Error: ' . $exception->getMessage() . '</p>' . '<pre>' . $exception->getTraceAsString() . '</pre>');
    } else {
      exit('404 error');
    }
  }
  
}
?>