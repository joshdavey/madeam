<?php
/**
 * Welcome to the application bootstrap file.
 */


# uncomment to enable unicode
mb_internal_encoding("UTF-8");


# uncomment to set content type to utf8
header ('Content-type: text/html; charset=utf-8');



# development environment settings
if (MADAEM_ENVIRONMENT == 'development') {

  # set error reporting level
  error_reporting(E_ALL);

}

# production environment settings
elseif (MADEAM_ENVIRONMENT == 'production') {

  error_reporting(0);

}


?>