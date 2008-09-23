<?php

class Madeam_Controller_Builder_Xml extends Madeam_Controller_Builder {

  public function buildView() {

    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('response');

    $this->array2Xml($this->data, $xml);

    $xml->endElement();

    $this->output = $xml->outputMemory(true);
    return $this->output;
  }

  public function missingView() {
    $this->buildView();
    return;
  }

  public function buildLayout() {
    return $this->output;
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