<?php
class madeam_registry {

	public $registry;

	private static $_instance = false;


  public static function instance() {
    if(!madeam_registry::$_instance) {
      madeam_registry::$_instance = new madeam_registry();
    }
    return madeam_registry::$_instance;
  }

	public function set($id, $value) {
		$this->registry[$id] = $value;
	}

	public function get($id) {
		if (isset($this->registry[$id])) {
		  return $this->registry[$id];
		} else {
		  return false;
		}
	}

	public function exists($id) {
    if (isset($this->registry[$id])) {
		  return true;
		} else {
		  return false;
		}
	}

	public function delete($id) {
	  if (isset($this->registry[$id])) {
		  unset($this->registry[$id]);
		}
	}

}

?>