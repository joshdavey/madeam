<?php

class Parser_Xml extends Madeam_Parser {

  public function renderView() {
    $data = $this->_controller->getData();

    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('page');

    $this->array2Xml($data, $xml);

    $xml->endElement();

    $this->_output = $xml->outputMemory(true);
    return $this->_output;
  }

  public function missingView() {
    $this->renderView();
    return;
  }

  public function renderLayout() {
    return $this->_output;
  }

  public function missingLayout() {
    return;
  }

  private function array2Xml($array, XmlWriter $xml) {
    foreach($array as $key => $value){
      if(is_array($value)){
        $xml->startElement($key);
        $this->array2Xml($value, $xml);
        $xml->endElement();
        continue;
      }
      $xml->writeElement($key, $value);
    }
  }

}
?>