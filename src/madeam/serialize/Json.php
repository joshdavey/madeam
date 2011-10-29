<?php
namespace madeam\serialize;

class Json {

  public static function encode($data) {
    $data = self::__object($data);
    \madeam\debug($data);
    return json_encode($data);
  }

  static private function __object($data) {
    if (is_array($data)) {
      foreach ($data as &$d) {
        $d = self::__object($d);
      }
    } elseif ($data instanceof \IteratorAggregate) {
      $data = (object) $data->getIterator();
    }

    return $data;
  }

  public static function decode($data) {
    return json_decode($data);
  }

}