<?php
class madeam_console extends madeam_cli {

  public function initialize() {
    array_shift($_SERVER['argv']);
    $args = $_SERVER['argv'];

    $console  = false;
    $command  = false;

    // get list of available consoles
    $consoles = array('make', 'create', 'delete', 'cache', 'test');

    do {
      // by entering a console name at this point it means they've tried entering one that doesn't exist.
      // prompt them with an saying please try again.
      if ($console !== false) {
        oute("Sorry the console you've entered does not exist.");
      }

      // reset console
      $console = false;

      // check to see if the console exists in the args
      if (isset($args[0])) { $console = array_shift($args); }

      if ($console == null) {
        // ask them for the name of the console they'd like to use

        $this->out_menu($consles);

        $console = $this->get_command('script');
      }

    } while (!in_array($console, $consoles));


    // get list of available commands
    $commands = array('controller', 'view', 'model', 'app');

    do {
      // by entering a console name at this point it means they've tried entering one that doesn't exist.
      // prompt them with an saying please try again.
      if ($command !== false) {
        oute("Sorry the command you've entered does not exist.");
      }

      // reset command
      $command = false;

      // check to see if the command exists in the args
      if (isset($args[0])) { $command = array_shift($args); }

      if ($command == null) {
        // ask them for the name of the console they'd like to use

        $this->out_menu($commands);

        $command = $this->get_command('command');
      }
    } while (!in_array($command, $commands));


    // execute commands
    if (execute_script_command($console, $command, $args) === true) {
      out();
      out("Success!");
    } else {
      out();
      out("Aborted");
    }

    // unset arguments -- they are only for first time use
    $args = array();
  }


}
?>