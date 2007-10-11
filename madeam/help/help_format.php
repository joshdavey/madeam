<?php
class help_format {

  function date($format, $date) {

    $timestamp = null;

    return date($format, time());
  }

  function money($amount) {
    return $amount;
  }

}
?>