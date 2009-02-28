<?php
class Madeam_Serialize_Xml {
  
  public static function encode($data) {
    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('data');
    
    self::_array2Xml($data, $xml);

    $xml->endElement();
    
    return $xml->outputMemory(false);
  }
  
  public static function decode($data) {
    
  }
  
  private static function _array2Xml($array, XmlWriter &$xml) {
    foreach($array as $key => $value) {
      $key = str_replace(" ", '_', $key);
      if(is_array($value)){
        $xml->startElement($key);
        self::_array2Xml($value, $xml);
        $xml->endElement();
        continue;
      }
      $xml->writeElement($key, $value);
    }
  }
  
}