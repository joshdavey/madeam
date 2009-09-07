<?php
switch (madeam\Framework::$environment) {
  case 'development' :
    madeam\Exception::$inlineErrors  = true;
    madeam\Exception::$debugMode     = true;
    
    activerecord\Configure::$connections = array(
      'default' => array(
        'master' => "mysql://username:password@localhost?name=madeam_development",
        'slaves' => array(
          "mysql://username:password@localhost?name=madeam_development",
          "mysql://username:password@localhost?name=madeam_development"
        )
      )
    );
  break;  
  case 'production' :
    madeam\Exception::$inlineErrors  = false;
    madeam\Exception::$debugMode     = false;
    
    activerecord\Configure::$connections = array(
      'master' => "mysql://username:password@localhost?name=madeam_production",
      'slaves' => array(
        "mysql://username:password@localhost?name=madeam_production",
        "mysql://username:password@localhost?name=madeam_production"
      )
    );
  break;
}

// add middleware
madeam\Framework::$middleware = array(
  // 'SessionsMiddleware',
  // 'TidyHtmlMiddleware'
);

// set default timezone
date_default_timezone_set('America/Toronto');