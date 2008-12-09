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

  /**
   * name of the database table or file resource that holds the records for this model
   *
   * @var unknown_type
   */
  public $resourceName = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $primaryKey = false;
  
  /**
   * Enter description here...
   *
   * @var array
   */
  protected $_data = array();

  /**
   * Enter description here...
   *
   * @var string
   */
  protected $fieldPrefix = null;

  /**
   * Enter description here...
   *
   * @var string
   */
  protected $resourcePrefix = null;

  /**
   * does the depth really need to be 2 by default? why not 1? We can save a lot of
   * processing time by making it 1 if that doesn't confuse people and it works as it should.
   *
   * @var unknown_type
   */
  protected $depth = 2;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $modelName = null;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $unbound = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $parent = false; // this is used when initiating a sub model within a model.

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $fields = array(); // list of fields to be included when returning result

	/**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $label = null; // list of fields to be included when returning result

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $cacheName = 'madeam.model.';

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $setup = array();
  
  /**
   * 
   */
  protected $_reflection = false;

  /**
   * Enter description here...
   *
   * @param unknown_type $depth
   */
  public function __construct($params = array()) {
    // set depth
    if (isset($params['depth'])) {
      $this->depth = $params['depth'];
    }

    // adjust depth
    // the depth measures how deep you want the relationships to go.
    if ($this->depth > 0) {
      $this->depth --;
    }
    
    if (isset($params['primaryKey'])) {
    	$this->primaryKey = $params['primaryKey'];
    }

    if (isset($params['name'])) {
      $this->modelName = $params['name'];
    } else {
      // set name
      $this->modelName = Madeam_Inflector::modelNameize(substr(get_class($this), 6)); // safely remove "Model_" from the name
    }

    if (isset($params['resourceName'])) {
      $this->resourceName = $params['resourceName'];
    }

    // set cache name
    $this->cacheName .= low($this->modelName) . '.setup';

    // clear model cache if it cache is disabled for routes
    if (!Madeam_Config::get('cache_models')) { 
      Madeam_Cache::clear($this->cacheName);	
    }

    // check cache for setup. if cache doesn't exist define it and then save it
    if (! $this->setup = Madeam_Cache::read($this->cacheName, - 1, Madeam_Config::get('ignore_model_cache'))) {
      
      // pre-load a reflection of this class for use in parseing the meta data and methods
      $reflection = new ReflectionClass(get_class($this));
      
      $this->loadSetup($reflection);
      
      unset($reflection);

      // save cache
      if (Madeam_Config::get('ignore_model_cache') === false) {
        Madeam_Cache::save($this->cacheName, $this->setup, true);
      }
    }
  }
  
  public function loadSetup($reflection) {
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
    // the resourceName parameter is available for the developer to change the resource name in
    // the model definition but $this->setup['resourceName'] is used through this class because it is cached
    if ($this->resourceName == null) {
      $this->setup['resourceName'] = Madeam_Inflector::modelTableize($this->modelName);
    } else {
      $this->setup['resourceName'] = $this->resourceName;
    }

    // set primaryKey
    // the primaryKey parameter is available for the developer to change the primary key in
    // the model definition but $this->setup['primaryKey'] is used through this class because it is cached
    if ($this->primaryKey !== false) {
      $this->setup['primaryKey'] = $this->primaryKey;
    }

    // load schema
    $this->loadSchema();
    
    // load standard fields
    $this->loadStandardFields();

    // pre-load custom fields
    $this->loadCustomFields($reflection);

    // load validators
    $this->loadValidators($reflection);

    // load callbacks
    $this->loadCallbacks($reflection);
  }
  
  public function __clone() {
  	$this->depth--;
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
  public function &__get($name) {
    // catch set_ method call
    $match = array();
    if (preg_match("/^[A-Z]{1}/", $name, $match) && in_array($name, array_keys($this->setup['hasModels']))) {
      $model = $this->setup['hasModels'][$name]['model'];
      // set model class name
      $modelClass = Madeam_Inflector::modelClassize($model);

      $modelSetup = array(array('depth' => $this->depth, 'name' => $name));

      // create model instance
      $inst = new $modelClass($modelSetup);
      $this->$name = $inst;
      
      // here we pass a reference of this class to the child model
      $this->$name->parent = $this;
      return $inst;
    }
    
    if (array_key_exists($name, $this->_data)) {
      return $this->_data[$name];
    } else {
     $this->_data[$name] = null;
     return $this->_data[$name];
    }
  }
    
  /**
   * load the standard fields from a schema
   *
   */
  final protected function loadStandardFields() {
    foreach ($this->setup['schema'] as $field) {
      $this->setup['standardFields'][] = $field['Field'];
      
      // set primary key
      if ($field['Key'] == 'PRI' && !isset($this->setup['primaryKey'])) {
        $this->setup['primaryKey'] = $field['Field'];
      }
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
   * This method parses all the class properties to find relationships
   */
  final protected function loadRelations($reflection) {
    $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    $matches = array();
    foreach ($props as $prop) {
      if (preg_match("/^(hasMany|hasOne|belongsTo|hasAndBelongsToMany)_(.+)/", $prop->name, $matches)) {
        $relationship = $matches[1];
        $model = $matches[2];
        $params = (array) $prop->getValue($this);
        $relationMethod = 'add' . ucfirst($relationship);
        $this->$relationMethod($model, $params);
      }
    }
    // merge models
    // and add itself to the list of models
    $this->setup['hasModels'] = array_merge($this->setup['hasOne'], $this->setup['hasMany'], $this->setup['hasAndBelongsToMany'], $this->setup['belongsTo'], array($this->modelName => array('model' => $this->modelName)));
  }

  /**
   * This method scans the names of this object's variables and creates a list of validators that are used when saving data.
   * For example the variable "var $validate_name_isnotempty = array('message' => 'Name cannot be empty')"
   * Sets a validator that validates the name's value with the validator method "isnotempty". If it fails to validate then
   * the error message "Name cannot be empty" is returned.
   *
   * You can also set arguments in the array. For example: "array('message' => 'Oops', 'max' => 255)".
   */
  final protected function loadValidators($reflection) {
    $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
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


  final protected function loadCallbacks($reflection) {
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | !ReflectionMethod::IS_FINAL);
    foreach ($methods as $method) {
      // callback properties (name, include, exclude)
      $callback = array();

      // set callback method name
      $callback['name'] = $method->getName();

      $matches = array();
      if (preg_match('/^((?:before|after)(?:Save|Validate|Update|Delete|Create|Find))(?:_[a-zA-Z0-9]*)?/', $method->getName(), $matches)) {

        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
          // set parameters of callback (parameters in methods act as meta data for callbacks)
          $callback[$parameter->getName()] = $parameter->getDefaultValue();
        }
        $this->setup[$matches[1]][] = $callback;
      }
    }
  }

  final protected function addHasAndBelongsToMany($model, $params) {
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($this->modelName) : false;
    // set associate's foreign key
    ! isset($params['associateForeignKey']) ? $params['associateForeignKey'] = Madeam_Inflector::modelForeignKey($model) : false;
    // set join model (table in the database that houses both foreign keys)
    $joinModels = array($params['model'], $this->modelName);
    asort($joinModels);
    $joinModel = implode($joinModels);
    ! isset($params['joinModel']) ? $params['joinModel'] = $joinModel : false;
    // set join resource name
    $params['joinResourceName'] = Madeam_Inflector::modelHabtm($this->modelName, $params['model']);    
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->setup['primaryKey'] : false;
    // set uniqueness
    isset($params['unique']) ? true : $params['unique'] = true;    
    $this->setup['hasAndBelongsToMany'][$params['model']] = $params;
  }

  final protected function addHasOne($model, $params) {
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($model) : false;
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->setup['primaryKey'] : false;
    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    $this->setup['hasOne'][$params['model']] = $params;
  }

  final protected function addHasMany($model, $params) {
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($this->modelName) : false;
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->setup['primaryKey'] : false;
    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    //t($params);
    $this->setup['hasMany'][$params['model']] = $params;
  }

  final protected function addBelongsTo($model, $params) {
    // set name of field that identifies the foreign record
    ! isset($params['foreignKey']) ? $params['foreignKey'] = Madeam_Inflector::modelForeignKey($model) : false;
    // set the model name
    ! isset($params['model']) ? $params['model'] = Madeam_Inflector::modelNameize($model) : $params['model'] = Madeam_Inflector::modelNameize($params['model']);
    // set primary key
    ! isset($params['primaryKey']) ? $params['primaryKey'] = $this->setup['primaryKey'] : false;
    // set dependency
    isset($params['dependent']) ? true : $params['dependent'] = true;
    $this->setup['belongsTo'][$params['model']] = $params;
  }

  /**
   * This method calls all the validation methods listed in the $validators variable and validates the values of a single entry
   */
  final protected function validateEntry($entry, $check_non_existent_fields = false) {
    $errors = array();
    foreach ($this->setup['validators'] as $validator) {
      $field = $validator['args']['field'];
      $method = $validator['method'];
      $errorKey = $this->modelName . Madeam::associationJoint . $field;
      
      // validate to make sure the validating method doesn't return false. If it does then save the error
      if ($check_non_existent_fields === false || isset($entry[$field])) {
        if (Madeam_Validate::$method(@$entry[$field], $validator['args']) === false) {
          //Madeam_Session::error($errorKey, $this->parseValidateMessage($validator['args']));
          $errors[$errorKey] = $this->parseValidateMessage($validator['args']);
        }
      }
    }
    
    return $errors;
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
    $newMessage = $args['message'];
    // find message variables
    preg_match_all('/\#([a-zA-Z_]+)/', $args['message'], $matchs);
    $vars = $matchs[1];
    // remove $message arg because if there is a string like #message it'll be replaced in an infinite loop!
    unset($args['message']);
    // replace each variable found in the message
    foreach ($vars as $var) {
      $newMessage = str_replace("#$var", $args[$var], $newMessage);
    }
    return $newMessage;
  }

  /**
   * New methods defined in models are actually custom fields. To add them to the entry being returned from a search
   * we need to execute the methods for each entry.
   *
   * This is probably a very costly method when calling lots of data. Need to find a way of turning it off when not needed.
   * Or atleast find a faster way of doing it.
   */
  final protected function prepareEntry($entry) {
    foreach ($this->setup['customFields'] as $field) {
      // excludes any fields that aren't in $this->fields
      if (in_array($field, $this->fields) || empty($this->fields)) {
        $entry[$field] = $this->$field();
      }
    }
    return $entry;
  }

  /**
   * New methods defined in models are actually custom fields. This method derives them by comparing the new methods to the old
   * methods to determine which ones are actually new
   */
  final protected function loadCustomFields($reflection) {
    // get the name of it's parent (example parents: activeRecord. activeFile, etc...)
    $parent = $reflection->getParentClass()->getName();
    
    // create instance of parent so we can compare the methods
    $parentReflection = new ReflectionClass($parent);
    
    // check each method to find out whethere it's a new field or not
    // I wish there was a faster way of doing this...
    $methods = $reflection->getMethods(ReflectionMethod::IS_PROTECTED);
    foreach ($methods as $method) {
      // get method name
      $methodName = $method->getName();
      if ($parentReflection->hasMethod($methodName) == false) {
        $this->setup['customFields'][] = $methodName;
      }
    }
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $name
   */
  final protected function _callback($name) {
    foreach ($this->setup[$name] as $callback) {
      $this->{$callback['name']}();
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
}
