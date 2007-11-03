<?php
/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class madeam_registry {

  /**
   * The registry is where all the data is stored to be available globally.
   *
   * @var array
   */
	public $registry;

	/**
	 * Stores this registry's instance
	 *
	 * @var boolean/madeam_registry object
	 */
	private static $_instance = false;


	/**
	 * Creates a new instance of the registry if it does not exist.
	 * If it does exist then it returns the already existing instance.
	 *
	 * @return madeam_registry object
	 */
  public static function instance() {
    if(!madeam_registry::$_instance) {
      madeam_registry::$_instance = new madeam_registry();
    }
    return madeam_registry::$_instance;
  }


  /**
   * Adds or modifies a an entry in the registry
   *
   * @param string $id
   * @param mixed $value
   */
	public function set($id, $value) {
		$this->registry[$id] = $value;
	}

	/**
	 * Returns a registry entry by id
	 *
	 * @param string $id
	 * @return mixed/boolean
	 */
	public function get($id) {
		if (isset($this->registry[$id])) {
		  return $this->registry[$id];
		} else {
		  return false;
		}
	}

	/**
	 * Checks to see if an entry exists by id
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function exists($id) {
    if (isset($this->registry[$id])) {
		  return true;
		} else {
		  return false;
		}
	}

	/**
	 * Removes an entry in the registry by id
	 *
	 * @param string $id
	 */
	public function delete($id) {
	  if (isset($this->registry[$id])) {
		  unset($this->registry[$id]);
		}
	}

}

?>