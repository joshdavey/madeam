<?php

class Madeam_Controller_Builder_Xml extends Madeam_Controller_Builder {

  public function missingView($view, $data) {
    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('response');

    $this->_array2Xml($data, $xml);

    $xml->endElement();
    
    return $xml->outputMemory(false);
  }

  public function buildLayouts($layouts, $data, $content) {
    return $content;
  }

  private function _array2Xml($array, XmlWriter $xml) {
    foreach($array as $key => $value){
      if(is_array($value)){
        $xml->startElement($key);
        $this->_array2Xml($value, $xml);
        $xml->endElement();
        continue;
      }
      $xml->writeElement($key, $value);
    }
  }

}