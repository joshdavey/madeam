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
      $this->setup['beforeSave'] = array();
      $this->setup['afterSave'] = array();
      $this->setup['beforeCreate'] = array();
      $this->setup['afterCreate'] = array();
      $this->setup['beforeUpdate'] = array();
      $this->setup['afterUpdate'] = array();
      $this->setup['beforeFind'] = array();
      $this->setup['afterFind'] = array();
      $this->setup['beforeValidate'] = array();
      $this->setup['afterValidate'] = array();
      $this->setup['beforeDelete'] = array();
      $this->setup['afterDelete'] = array();

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

      // load callbacks
      $this->loadCallbacks();

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
    $props = $this->reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    $matches = array();
    foreach ($props as $prop) {
      // get property name
      $propertyName = $prop->name;
      if (preg_match("/validate_(.+)/", $propertyName, $matches)) {
        // get value of validator property
        $args = $prop->getValue($this);
        // seperate bits of validate call by _
        $validate = explode('_', $matches[1]);
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
    $matches = array();
    foreach ($props as $prop) {
      $propertyName = $prop->name;
      if (preg_match("/field_(.+)/", $propertyName, $matches)) {
        $fieldName = $matches[1];
        $fields[$fieldName] = $this->{'field_' . $fieldName};
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
    $props = $this->reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    $matches = array();
    foreach ($props as $prop) {
      if (preg_match("/^(hasMany|hasOne|belongsTo|hasAndBelongsToMany)_(.+)/", $prop->name, $matches)) {
        $relationship = $matches[1];
        $model = $matches[2];
        $params = (array) $prop->getValue($this);
        $this->{'add' . ucfirst($relationship)}($model, $params);
      }
    }
    // merge models
    // and add itself to the list of models
    $this->setup['hasModels'] = array_merge($this->setup['hasOne'], $this->setup['hasMany'], $this->setup['hasAndBelongsToMany'], $this->setup['belongsTo'], array($this->name => array('model' => $this->name)));
  }

  final protected function loadCallbacks() {
    $props = $this->reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    $matches = array();
    foreach ($props as $prop) {
      if (preg_match("/^(before|after)(Save|Validate|Update|Delete|Create|Find)_(.+)/", $prop->name, $matches)) {
        $when   = $matches[1];
        $action = $matches[2];
        $method = $matches[3];
        $params = (array) $prop->getValue($this);
        $this->setup[$when.$action][$method] = $params;
      }
    }
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
    $methods = $this->reflection->getMethods(ReflectionMethod::IS_PROTECTED || !ReflectionMethod::IS_FINAL);
    foreach ($methods as $method) {
      // get method name
      $methodName = $method->getName();
      if (substr($methodName, 0, 1) != '_' && $parentReflection->hasMethod($methodName) == false) {
        $this->setup['customFields'][] = $methodName;
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
