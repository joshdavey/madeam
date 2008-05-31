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
class Madeam_Model {

  protected $resourceName = null; // name of the database table or file resource that holds the records for this model

  protected $depth = 2; // does the depth really need to be 2 by default? why not 1? We can save a lot of processing time by making it 1 if that doesn't confuse people and it works as it should.

  protected $reflection;

  protected $name = null; // -- this needs to be re-named to _name and made protected

  protected $unbound = array();

  protected $primaryKey = 'id';

  protected $parent = false; // this is used when initiating a sub model within a model.

  protected $fields = array(); // list of arrays to be included when returning result

  protected $cacheName = 'madeam.model.';

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
   * primaryKey
   *	this model's primary key -- defaults to "id"
   *
   *
   */
  protected $setup = array();

  public function __construct($depth = false) {
    // set depth
    if ($depth !== false) {
      $this->depth = $depth;
    }

    // adjust depth
    // the depth measures how deep you want the relationships to go.
    if ($this->depth > 0) {
      $this->depth --;
    }

    // get class name
    $modelname = get_class($this);

    // set name
    $this->name = Madeam_Inflector::modelNameize(substr($modelname, 6)); // safely remove "Model_" from the name

    // check cache for schema
    // if schema not cached get it from the database using describe()
    // the cache should be infinite if cache is enabled
    $this->cacheName .= $modelname . '.setup';
    if (! $this->setup = Madeam_Cache::read($this->cacheName, - 1)) {
      $this->setup['hasMany'] = array();
      $this->setup['hasOne'] = array();
      $this->setup['belongsTo'] = array();
      $this->setup['hasAndBelongsToMany'] = array();
      $this->setup['hasModels'] = array(); // why is it called has_models? Change this please. Relationships maybe?
      $this->setup['customFields'] = array(); // custom fields defined in model
      $this->setup['standardFields'] = array(); // default fields in the database or file system
      $this->setup['validators'] = array();

      // set resourceName
      if ($this->resourceName == null) {
        $this->setup['resourceName'] = Madeam_Inflector::modelTableize($this->name);
      } else {
        $this->setup['resourceName'] = $this->resourceName;
      }

      // pre-load a reflection of this class for use in parseing the meta data and methods
      $this->loadReflection();

      // this parses the class properties to find relationships to other models, eventually populating has_many, has_one, has_and_belongs_to_many, etc...
      $this->loadRelations();

      // pre-load custom fields
      $this->loadCustomFields();

      // load schema
      $this->loadSchema();

      // load standard fields
      $this->loadStandardFields();

      // load validators
      $this->loadValidators();

      if (Madeam_Config::get('cache_models') === true) {
        Madeam_Cache::save($this->cacheName, $this->setup, true);
      }
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
    if (preg_match("/^[A-Z]{1}/", $name, $match) && in_array($name, array_keys($this->setup['hasModels']))) {
      $model = $this->setup['hasModels'][$name]['model'];
      // set model class name
      $model_class = Madeam_Inflector::modelClassize($model);
      // create model instance
      $inst = new $model_class($this->depth);
      $this->$name = $inst;
      // here we pass a reference of this class to the child model
      $this->$name->parent = $this;
      return $inst;
    }
  }

  /**
   * load the standard fields from a schema
   *
   */
  final public function loadStandardFields() {
    foreach ($this->setup['schema'] as $field) {
      $this->setup['standardFields'][] = $field['Field'];
    }
  }

  /**
   * load a schema
   *
   */
  final public function loadSchema() {
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
  final protected function loadValidators() {
    foreach ($this->reflection->getProperties() as $prop) {
      // filter out private variables. The less we need to parse the better
      if ($prop->isPublic()) {
        // get property name
        $property_name = $prop->name;
        if (preg_match("/validate_(.+)/", $property_name, $found)) {
          // get value of validator property
          $args = $prop->getValue($this);
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
  final protected function loadFields() {
    $fields = array();
    $props = $this->reflection->getProperties();
    foreach ($props as $prop) {
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
  final protected function loadRelations() {
    $props = $this->reflection->getProperties();
    foreach ($props as $prop) {
      // ignore private properties so we don't need to parse every single variable
      if ($prop->isPublic()) {
        if (preg_match("/^(hasMany|hasOne|belongsTo|hasAndBelongsToMany)_(.+)/", $prop->name, $found)) {
          $relationship = $found[1];
          $model = $found[2];
          $params = (array) $prop->getValue($this);
          $this->{'add' . ucfirst($relationship)}($model, $params);
        }
      }
    }
    // merge models
    // and add itself to the list of models
    $this->setup['hasModels'] = array_merge($this->setup['hasOne'], $this->setup['hasMany'], $this->setup['hasAndBelongsToMany'], $this->setup['belongsTo'], array($this->name => array('model' => $this->name)));
  }

  final protected function addHasAndBelongsToMany($model, $params) {
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($this->name) : false;
    // set associate's foreign key
    ! isset($params['associateForeignKey']) ? $params['associateForeignKey'] = Madeam_Inflector::modelForeignKey($model) : false;
    // set join model (table in the database that houses both foreign keys)
    ! isset($params['joinModel']) ? $params['joinModel'] = Madeam_Inflector::modelHabtm($model, $this->name) : false;
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->primaryKey : false;
    // set uniqueness
    isset($params['unique']) ? true : $params['unique'] = true;
    $this->setup['hasAndBelongsToMany'][Madeam_Inflector::modelNameize($model)] = $params;
  }

  final protected function addHasOne($model, $params) {
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($model) : false;
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->primaryKey : false;
    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    $this->setup['hasOne'][Madeam_Inflector::modelNameize($model)] = $params;
  }

  final protected function addHasMany($model, $params) {
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($this->name) : false;
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->primaryKey : false;
    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    //t($params);
    $this->setup['hasMany'][Madeam_Inflector::modelNameize($model)] = $params;
  }

  final protected function addBelongsTo($model, $params) {
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($model) : false;
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->primaryKey : false;
    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    $this->setup['belongsTo'][Madeam_Inflector::modelNameize($model)] = $params;
  }

  /**
   * Because we have so many methods that require the reflection instance of this class we have this method that
   * pre-loads it when the object is constructed
   */
  final protected function loadReflection() {
    $this->reflection = new ReflectionClass(get_class($this));
  }

  /**
   * This method calls all the validation methods listed in the $validators variable and validates the values of a single entry
   */
  final protected function validateEntry($check_non_existent_fields = false) {
    foreach ($this->setup['validators'] as $validator) {
      $field = $validator['args']['field'];
      $method = 'validate' . ucfirst($validator['method']);
      $error_key = $this->name . MADEAM_ASSOCIATION_JOINT . $field;
      // testing new Validation class
      //test('Valid? ' . Madeam_Validate::$method($this->entry[$field], $args));
      // validate to make sure the validating method doesn't return false. If it does then save the error
      if ($check_non_existent_fields === false || isset($this->entry[$field])) {
        if ($this->$method(@$this->entry[$field], $validator['args']) === false) {
          $this->session->error($error_key, $this->parseValidateMessage($validator['args']));
          //$_SESSION[MADEAM_MADEAM_USER_ERROR_NAME][$error_key][] = $this->parseValidateMessage($validator['args']);
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
  final protected function parseValidateMessage($args = array()) {
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
  final protected function prepareResults() {
    foreach ($this->setup['customFields'] as $field) {
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
  final protected function loadCustomFields() {
    // get the name of the model's instance.
    //$reflection = new ReflectionClass(get_class($this));
    // get the name of it's parent (example parents: activeRecord. activeFile, etc...)
    $parent = $this->reflection->getParentClass()->getName();
    // create instance of parent so we can compare the methods
    $parentReflection = new ReflectionClass($parent);
    // check each method to find out whethere it's a new field or not
    // I wish there was a faster way of doing this...
    $methods = $this->reflection->getMethods();
    foreach ($methods as $method) {
      // make sure this method is either not a final method or is public so we don't need to parse every single method
      if (! $method->isFinal() && $method->isProtected()) {
        // get method name
        $methodName = $method->getName();
        if (substr($methodName, 0, 1) != '_' && $parentReflection->hasMethod($methodName) == false) {
          $this->setup['customFields'][] = $methodName;
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
      $this->unbound[] = Madeam_Inflector::modelNameize($model);
    }
    return $this;
  }

  final public function unbindAll() {
    $exceptions = array();
    $unbound = array_keys($this->setup['hasModels']);
    if (func_num_args() > 0) {
      foreach (func_get_args() as $model) {
        $exceptions[] = Madeam_Inflector::modelNameize($model);
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
   * Callback functions
   * =======================================================================
   */
  protected function beforeSave() {
    return true;
  }

  protected function afterSave() {
    return true;
  }

  protected function beforeDelete() {
    return true;
  }

  protected function afterDelete() {
    return true;
  }

  protected function beforeValidation() {
    return true;
  }

  protected function afterValidation() {
    return true;
  }

  protected function beforeFind() {
    return true;
  }

  protected function afterFind() {
    return true;
  }

  /**
   * Getter functions
   * =======================================================================
   */
  public function getPrimaryKey() {
    return $this->primaryKey;
  }

  public function getSetup() {
    return $this->setup;
  }
}
