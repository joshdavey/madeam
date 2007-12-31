<?php
class Madeam_Error {
  
  const ERR_CRITICAL  = 0;
  const ERR_NOT_FOUND = 50;
  
  private static $helpfulReminders = array(
		"Don't worry, be happy. It could be worse.",
		"Oops. Did someone make a boo boo?",
		"You should really fix this.",
		"Just blame Josh Davey.",
		"This is the last time I trust open source software.",
		"Did you intend on launching a nuclear missile? Because it's too late to stop it.",
		"This is neither a horse, or a stable.",
		"What have you done!?",
		"Oh @%&#",
		"The tech bubble burst! Run, save yourself!",
		"Is this your idea of web 3.0?"
	);
  
  public static function catchException(Exception $exception, $code = 100) {
    if (MADEAM_ENABLE_DEBUG === true) {
      $reminder = rand(0, count(self::$helpfulReminders)-1);
      exit(
      '<p>' . self::$helpfulReminders[$reminder] . '<hr />' . 
      '<b>Error:</b> <p>' . $exception->getMessage() . '</p>' . 
      '<b>Backtrace:</b>' . 
      '<pre>' . $exception->getTraceAsString() . '</pre>');
    } else {
      exit('404 error');
    }
  }
  
}
?>