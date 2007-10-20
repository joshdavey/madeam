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
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class madeam_model {

  protected $resource_name          = null;  // name of the database table or file resource that holds the records for this model
  protected $depth                  = 2;  // does the depth really need to be 2 by default? why not 1? We can save a lot of processing time by making it 1 if that doesn't confuse people and it works as it should.
  protected $reflection_obj;
  protected $name                      = null; // -- this needs to be re-named to _name and made protected
  protected $unbound                = array();
  protected $primary_key            = 'id';
  protected $parent                 = false;    // this is used when initiating a sub model within a model.
  protected $fields                 = array();  // list of arrays to be included when returning result

  /**
   * This member variable is where all the model setup information is stored.
   *
   * behaviors
   *	array of behaviors
   *
   * custom_fields
   * 	array of custom fields
   *
   * standard_fields
   *  array of standard_fields
   *
   * schema
   *	array of fields and their properties that exist prior to the custom fields
   *
   * has_one
   *	array of has_one relationships
   *
   * has_many
   *	array of has_many relationships
   *
   * belongs_to
   *	array of belongs_to relationships
   *
   * has_and_belongs_to_many
   *	array of has_and_belongs_to_many relationships
   *
   * has_models
   *	array of all relationships
   *
   * primary_key
   *	this model's primary key -- defaults to "id"
   *
   *
   */
  protected $setup								= array();

  public function __construct($depth = false) {
    // set depth
    if ($depth !== false) { $this->depth = $depth; }

    // adjust depth
    // the depth measures how deep you want the relationships to go.
    if ($this->depth > 0) { $this->depth--; }

    // get class name
    $modelname = get_class($this);

    // set name
    $this->name = madeam_inflector::model_nameize(substr($modelname, 6)); // safely remove "Model" from the name

    // check cache for schema
    // if schema not cached get it from the database using describe()
    // the cache should be infinite if cache is enabled
    $this->cache_name = $modelname . '_setup';
    if (!$this->setup = madeam_cache::read($this->cache_name, -1)) {
      $this->setup['has_many']                 = array();
      $this->setup['has_one']                  = array();
      $this->setup['belongs_to']               = array();
      $this->setup['has_and_belongs_to_many']  = array();
      $this->setup['has_models']               = array(); // why is it called has_models? Change this please. Relationships maybe?
      $this->setup['custom_fields']            = array(); // custom fields defined in model
	    $this->setup['standard_fields']          = array(); // default fields in the database or file system
	    $this->setup['validators']               = array();

	    // set resource_name
      if ($this->resource_name == null) {
        $this->setup['resource_name'] = madeam_inflector::model_tableize($this->name);
      } else {
        $this->setup['resource_name'] = $this->resource_name;
      }

      // pre-load a reflection of this class for use in parseing the meta data and methods
      $this->load_reflection();

      // this parses the class properties to find relationships to other models, eventually populating has_many, has_one, has_and_belongs_to_many, etc...
      $this->load_relations();

      // pre-load custom fields
      $this->load_custom_fields();

      // load schema
      $this->load_schema();

      // load standard fields
      $this->load_standard_fields();

      // load validators
      $this->load_validators();

      madeam_cache::save($this->cache_name, $this->setup, true);
    }
  }


  /**
   * Handle variable gets.
   * This magic method exists to handle instances of models when they're called
   * If the instance of a model doesn't exist we create it here.
   * This is so we don't need to pre-load all of them.
   *
   * @param string $name
   * @return object
   */
  public function __get($name) {
    // catch set_ method call
    $match = array();
    if (preg_match("/^[A-Z]{1}/", $name, $match) && in_array($name, array_keys($this->setup['has_models']))) {

      $model = $this->setup['has_models'][$name]['model'];

      // set model class name
      $model_class = madeam_inflector::model_classize($model);

      // create model instance
      $inst = new $model_class($this->depth);
      $this->$name = $inst;

      // here we pass a reference of this class to the child model
      $this->$name->parent = $this;

      return $inst;
    } else {
      // set model class name
      $comp_class = 'component_' . $name;

      // create component instance
      $inst = new $comp_class($this);
      $this->$name = $inst;
      return $inst;
    }
  }

  /**
   * load the standard fields from a schema
   *
   */
  public function load_standard_fields() {
    foreach ($this->setup['schema'] as $field) {
      $this->setup['standard_fields'][] = $field['Field'];
    }
  }

  /**
   * load a schema
   *
   */
  public function load_schema() {
    $this->setup['schema'] = $this->describe();
  }

  /**
   * This method scans the names of this object's variables and creates a list of validators that are used when saving data.
   * For example the variable "var $validate_name_isnotempty = array('message' => 'Name cannot be empty')"
   * Sets a validator that validates the name's value with the validator method "isnotempty". If it fails to validate then
   * the error message "Name cannot be empty" is returned.
   *
   * You can also set arguments in the array. For example: "array('message' => 'Oops', 'max' => 255)".
   */
  final protected function load_validators() {
    foreach ($this->reflection_obj->getProperties() as $prop) {

			// filter out private variables. The less we need to parse the better
			if ($prop->isPublic() || $prop->isProtected()) {
				// get property name
				$property_name = $prop->name;

				if (preg_match("/validate_(.+)/", $property_name, $found)) {
					// get value of validator property
					$args  = $prop->getValue($this);

					// seperate bits of validate call by _
					$validate = explode('_', $found[1]);

					// get method name
					$method = $validate[count($validate) - 1];

					// remove method name from the end of validate var
					array_pop($validate);

					// implode remains of $validate to get field name
					$field = implode('_', $validate);

					// add to validator list
					$this->validator($field, $method, $args);
				}
			}
    }
  }

  /**
   * Set a validator
   *
   * @param string $field
   * @param string $method
   * @param array $args
   */
  final protected function validator($field, $method, $args = array()) {
    $args['field'] = $field;
    $this->setup['validators'][] = array('method' => $method, 'args' => $args);
  }

  /**
   * Just an idea... How do you feel about setting up models all in the class?
   * It'd be better if we didn't always have to load fields for every model that's related to this model.
   * Can we make it so it only does it for the root model or do we need the other fields when creating forms?
   */
  final protected function load_fields() {
    $fields = array();
    foreach ($this->reflection_obj->getProperties() as $prop) {
      $property_name = $prop->name;
      if (preg_match("/field_(.+)/", $property_name, $found)) {
        $field_name = $found[1];
        $fields[$field_name] = $this->{'field_' . $field_name};
      }
    }

   // it's called skeleton because $this->setup['schema'] is already being used for something else and this data set
   // represents the structure or skeleton of the model
   $this->setup['schema'] = $fields;
  }

  /**
   * This method parses all the class properties to find relationships
   */
  final protected function load_relations() {
    foreach ($this->reflection_obj->getProperties() as $prop) {
			// ignore private properties so we don't need to parse every single variable
			if ($prop->isPublic() || $prop->isProtected()) {
				if (preg_match("/^(has_many|has_one|belongs_to|has_and_belongs_to_many)_(.+)/", $prop->name, $found)) {
					$relationship = $found[1];
					$model        = $found[2];
					$params       = (array) $prop->getValue($this);

					$this->{'add_' . $relationship}($model, $params);
				}
			}
    }

    // merge models
    // and add itself to the list of models
    $this->setup['has_models'] = array_merge($this->setup['has_one'], $this->setup['has_many'], $this->setup['has_and_belongs_to_many'], $this->setup['belongs_to'], array($this->name => array('model' => $this->name)));
  }

  final protected function add_has_and_belongs_to_many($model, $params) {
    // set the model name
    @$params['model'] == null ? $params['model'] = madeam_inflector::model_nameize($model) : $params['model'] = madeam_inflector::model_nameize($params['model']);

    // set name of field that identifies the foreign record
    @$params['foreign_key'] == null ? $params['foreign_key'] = madeam_inflector::model_foreign_key($this->name) : false;

    // set associate's foreign key
    @$params['associate_foreign_key'] == null ? $params['associate_foreign_key'] = madeam_inflector::model_foreign_key($model) : false;

    // set join model (table in the database that houses both foreign keys)
    @$params['join_model'] == null ? $params['join_model'] = madeam_inflector::model_habtm($model, $this->name) : false;

    // set primary key
    @$params['primary_key'] == null ? $params['primary_key'] = $this->primary_key : false;

    // set uniqueness
    isset($params['unique']) ? true : $params['unique'] = true;

    $this->setup['has_and_belongs_to_many'][madeam_inflector::model_nameize($model)] = $params;
  }

  final protected function add_has_one($model, $params) {
    // set name of field that identifies the foreign record
    @$params['foreign_key'] == null ? $params['foreign_key'] = madeam_inflector::model_foreign_key($model) : false;

    // set the model name
    @$params['model'] == null ? $params['model'] = madeam_inflector::model_nameize($model) : $params['model'] = madeam_inflector::model_nameize($params['model']);

    // set primary key
    @$params['primary_key'] == null ? $params['primary_key'] = $this->primary_key : false;

    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;

    $this->setup['has_one'][madeam_inflector::model_nameize($model)] = $params;
  }

  final protected function add_has_many($model, $params) {
    // set name of field that identifies the foreign record
    @$params['foreign_key'] == null ? $params['foreign_key'] = madeam_inflector::model_foreign_key($this->name) : false;

    // set the model name
    @$params['model'] == null ? $params['model'] = madeam_inflector::model_nameize($model) : $params['model'] = madeam_inflector::model_nameize($params['model']);

    // set primary key
    @$params['primary_key'] == null ? $params['primary_key'] = $this->primary_key : false;

    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    //t($params);
    $this->setup['has_many'][madeam_inflector::model_nameize($model)] = $params;
  }

  final protected function add_belongs_to($model, $params) {
    // set name of field that identifies the foreign record
    @$params['foreign_key'] == null ? $params['foreign_key'] = madeam_inflector::model_foreign_key($model) : false;

    // set the model name
    @$params['model'] == null ? $params['model'] = madeam_inflector::model_nameize($model) : $params['model'] = madeam_inflector::model_nameize($params['model']);

    // set primary key
    @$params['primary_key'] == null ? $params['primary_key'] = $this->primary_key : false;

    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;

    $this->setup['belongs_to'][madeam_inflector::model_nameize($model)] = $params;
  }

  /**
   * Because we have so many methods that require the reflection instance of this class we have this method that
   * pre-loads it when the object is constructed
   */
  final protected function load_reflection() {
    $this->reflection_obj = new ReflectionClass(get_class($this));
  }

  /**
   * This method calls all the validation methods listed in the $validators variable and validates the values of a single entry
   */
  final protected function validate_entry($check_non_existent_fields = false) {
    foreach ($this->setup['validators'] as $validator) {
      $field    = $validator['args']['field'];
      $method   = 'validate' . $validator['method'];

      $error_key = $this->name . MODEL_JOINT . $field;

      // validate to make sure the validating method doesn't return false. If it does then save the error
      if ($check_non_existent_fields === false || isset($this->entry[$field])) {
        if ($this->$method(@$this->entry[$field], $validator['args']) === false) {
					$this->session->error($error_key, $this->parse_validate_message($validator['args']));
          //$_SESSION[USER_ERROR_NAME][$error_key][] = $this->parse_validate_message($validator['args']);
        }
      }
    }
  }

  /**
   * Searches for message variables that look like this "#varname" and replaces them with the values assigned
   * in the message arguments.
   *
   * @param array $args
   * @return string
   */
  final protected function parse_validate_message($args = array()) {
    // set new message
    $new_message = $args['message'];

    // find message variables
    preg_match_all('/\#([a-zA-Z_]+)/', $args['message'], $matchs);
    $vars = $matchs[1];

    // remove $message arg because if there is a string like #message it'll be replaced in an infinite loop!
    unset($args['message']);

    // replace each variable found in the message
    foreach ($vars as $var) {
      $new_message = str_replace("#$var", $args[$var], $new_message);
    }

    return $new_message;
  }

  /**
   * New methods defined in models are actually custom fields. To add them to the entry being returned from a search
   * we need to execute the methods for each entry.
   *
   * This is probably a very costly method when calling lots of data. Need to find a way of turning it off when not needed.
   * Or atleast find a faster way of doing it.
   */
  final protected function prepare_result() {
    foreach ($this->setup['custom_fields'] as $field) {
      // include all fields if $this->fields is empty
      if (empty($this->fields)) {
        //$this->entry[$field] = $this->$field(@$this->entry[$field]);
        $this->entry[$field] = $this->$field();
      } else {
        // include only fields that have been listed in the fields list if the list is not empty
        if (in_array($field, $this->fields)) {
          //$this->entry[$field] = $this->$field(@$this->entry[$field]); // cool idea to differentiate between custom fields and existing fields -- and handy
          $this->entry[$field] = $this->$field();
        }
      }
    }
  }

  /**
   * New methods defined in models are actually custom fields. This method derives them by comparing the new methods to the old
   * methods to determine which ones are actually new
   */
  final protected function load_custom_fields() {
    // get the name of the model's instance.
    //$reflection_obj = new ReflectionClass(get_class($this));

    // get the name of it's parent (example parents: activeRecord. activeFile, etc...)
    $parent = $this->reflection_obj->getParentClass()->getName();

    // create instance of parent so we can compare the methods
    $parent_relf  = new ReflectionClass($parent);

    // check each method to find out whethere it's a new field or not
    // I wish there was a faster way of doing this...
    foreach ($this->reflection_obj->getMethods() as $model_method) {
			// make sure this method is either not a final method or is public so we don't need to parse every single method
			if (!$model_method->isFinal() && $model_method->isPublic()) {
				// get method name
				$model_method_name = $model_method->getName();
				if (substr($model_method_name, 0, 1) != '_' && $parent_relf->hasMethod($model_method_name) == false) {
					$this->setup['custom_fields'][] = $model_method_name;
				}
			}
    }
  }

  protected function describe() {
    return array();
  }

  /**
   * Query Methods
   * =======================================================================
   */
  final public function unbind() {
    foreach (func_get_args() as $model) {
      $this->unbound[] = madeam_inflector::model_nameize($model);
    }

    return $this;
  }

  final public function unbind_all() {
		$exceptions = array();
		$unbound 		= array_keys($this->setup['has_models']);

		if (func_num_args() > 0) {
			foreach (func_get_args() as $model) {
				$exceptions[] = madeam_inflector::model_nameize($model);
			}
		}

		// exclude exceptions
		$this->unbound = array_diff($unbound, $exceptions);

    return $this;
  }

  final public function bind($model, $relation, $params) {
    $this->{'add_' . $relation}($model, $params);

    return $this;
  }


  /**
   * Validating Methods
   * =======================================================================
   */

  final protected function validateLength($v, $args) {
    $len = strlen($v);
    $min = (int) $args['min'];
    $max = (int) $args['max'];
    if (($len >= $min && $len <= $max) || ($len >= $min && $max == 0)) {
      return true;
    }

    return false;
  }

  final protected function validateIsnotequal($v, $args) {
    $not_values = $args['value'];
    if (is_array($not_values)) {
      if (!in_array($v, $not_values)) {
        return true;
      }
      return false;
    } else {
      if ($not_values != $v) {
        return true;
      } else {
        return false;
      }
    }
  }

  final protected function validateIsdatetime($v, $args) {
    return true;
  }

  final protected function validateIsnotempty($v, $args) {
    if ($v == null) {
      return false;
    }
    return true;
  }

  final protected function validateIsemail($v, $args) {
    if (!preg_match('/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/', $v)) {
      return false;
    }
    return true;
  }

  final protected function validateIsexpression($v, $args) {
    if (!preg_match($args['regexp'], $v)) {
      return false;
    }
    return true;
  }

  final protected function validateIsisbn($v, $args) {
    if (!preg_match('/^(97(8|9))?\d{9}(\d|X)$/', $v)) {
      return false;
    }
    return true;
  }

  final protected function validateIsUnique($v, $args) {

  }

  /**
   * Callback functions
   * =======================================================================
   */

  public function before_save() {
    return true;
  }

  public function after_save() {
    return true;
  }

  public function before_delete() {
    return true;
  }

  public function after_delete() {
    return true;
  }

  public function before_validation() {
    return true;
  }

  public function after_validation() {
    return true;
  }

  public function before_find() {
    return true;
  }

  public function after_find() {
    return true;
  }

  /**
   * Getter functions
   * =======================================================================
   */

  public function get_primary_key() {
   return $this->primary_key;
  }

  public function get_setup() {
    return $this->setup;
  }

}
?>