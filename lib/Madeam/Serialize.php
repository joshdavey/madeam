<?php
class Madeam_Serialize {
  
  public static function encodeJson($data) {
    return json_encode($data);
  }
  
  public static function decodeJson($json) {
    return json_decode($json);
  }
  
  public static function encodeSphp($data) {
    return serialize($data);
  }
  
  public static function decodeSphp($php) {
    return unserialize($php);
  }
  
  public static function encodeXml($data) {
    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('response');

    self::_array2Xml($data, $xml);

    $xml->endElement();
    
    return $xml->outputMemory(false);
  }
  
  private static function _array2Xml($array, XmlWriter &$xml) {
    foreach($array as $key => $value){
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