<?php
class controller_index extends controller_app {

  public function index() {
    // welcome to the index controller's index action
  }

}

class madeam_iterator implements Iterator {

  public function __set($name, $value) {
    $this->$name = $value;
    return $value;
  }

  public function current() {
    return current($this);
  }

  public function key() {
    return key($this);
  }

  public function next() {
    return next($this);
  }

  public function rewind() {
    return reset($this);
  }

  public function valid() {
    return (current($this) !== false);
  }

}

/*
// just thinking...
$query = mysql_query('select * from posts');
mysql_fetch_object($query, 'madeam_iterator');

$a = new madeam_iterator();
$a->test = 'testing';
$a->name = 'awesome';
echo $a->next();
echo $a->next();
echo $a->name;
*/
?>