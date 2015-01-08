<?php
namespace madeam\serialize;

class Sphp {

  public static function encode($data) {
    return serialize($data);
  }

  public static function decode($data) {
    return unserialize($data);
  }

}