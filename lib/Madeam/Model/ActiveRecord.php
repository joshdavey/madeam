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
class Madeam_Model_ActiveRecord extends Madeam_Model {

  /**
   * Represents a single row
   *
   * @var array
   */
  protected $entry = array();

  /**
   * The name of the server configuration we want to use.
   *
   * @var string/integer
   */
  private $_server = 0;
  
  /**
   * pdo instance
   *
   * @var resource/boolean
   */
  private static $_pdo = array();

  /**
   * Enter description here...
   *
   * @var boolean
   */
  private $_sqlStart = false;

  /**
   * Fields in the query
   *
   * @var array
   */
  private $_sqlFields = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $_sqlRange = false;

  /**
   * Enter description here...
   *
   * @var string/boolean
   */
  private $_sqlOrder = false;

  /**
   * Enter description here...
   *
   * @var string
   */
  private $_sqlWhere = false;

  /**
   * Enter description here...
   *
   * @var array
   */
  private $_sqlJoins = array();

  /**
   * Enter description here...
   *
   * @var string/boolean
   */
  private $_sqlGroup = false;

  /**
   * Enter description here...
   *
   * @var string/boolean
   */
  private $_sqlHaving = false;

  public function loadSetup($reflection) {
    parent::loadSetup($reflection);
    
    // this parses the class properties to find relationships to other models, eventually populating has_many, has_one, has_and_belongs_to_many, etc...
    $this->loadRelations($reflection);
  }

  /**
   * Magic method allows for special query functions like findAll_by_name('Joshua');
   *
   * @param string $name
   * @param array $args
   * @return array/boolean
   */
  public function __call($name, $args) {
    $match = array();
    if (preg_match("/^find([a-zA-Z]+)By_(.*)/", $name, $match)) {
      $this->where(array($this->modelName . '.' . $match[2] => $args[0]));
      $function = 'find' . $match[1];
      if (method_exists($this, $function)) {
        return $this->$function();
      }
    } elseif (preg_match("/^deleteBy_(.*)/", $name, $match)) {
      $this->where(array($this->modelName . '.' . $match[2] => $args[0]));
      $this->delete();
    } else {
      $backtrace = debug_backtrace();
      throw new Madeam_Exception_MissingMethod("See line <strong>" . $backtrace[1]['line'] . "</strong> in <strong>" . $backtrace[1]['file'] . "</strong> \n Unknown method " . $name . ' in ' . get_class($this) . " model.");
    }

    return false;
  }

  public function __set($name, $value) {
    if (!preg_match('/^(?:[A-Z])/', $name)) {
      $this->_data[$name] = $value;
    } else {
      $this->$name = $value;
    }
  }
  
  public function __isset($name) {
    if (isset($this->_data[$name])) {
      return true;
    } else {
      return false;
    }
  }
  
  public function __unset($name) {
    unset($this->_data[$name]);
  }
  
  /**
   * Query Methods
   * =======================================================================
   */
   
  /**
   * Enter description here...
   *
   * @param string $sql
   * @return resource
   */
  final public function execute($sql) {
    $count = 0;
    $_servers = Madeam_Config::get('connections');
    $this->connect($_servers[$this->_server]);
    
    try {
      // log -- I hate using Madeam_Logger for this stuff... can't we catch it all somewhere?
      Madeam_Logger::getInstance()->log($sql);

      // link
      $count = self::$_pdo[$this->_server]->exec($sql);

    } catch (PDOException $e) {
      if (Madeam_Config::get('enable_debug') == true) {
        $trace = $e->getTrace();
        $error = self::$_pdo[$this->_server]->errorInfo();
        Madeam_Exception::catchException($e, array('message' => 'See line <strong>' . $trace[2]['line'] . '</strong> in <strong>' . $trace[2]['class'] . "</strong> \n" . $error[2] . "\n" . $sql));
      } else {
        return 0;
      }
    }

    return $count;
  }
  
  final public function query($sql) {    
    $conns = Madeam_Config::get('connections');
    $this->connect($conns[$this->_server]);
    
    try {
      // log -- I hate using Madeam_Logger for this stuff... can't we catch it all somewhere?
      Madeam_Logger::getInstance()->log($sql);

      // link
      $link = self::$_pdo[$this->_server]->query($sql, true);

    } catch (PDOException $e) {
      $trace = $e->getTrace();
      $error = self::$_pdo[$this->_server]->errorInfo();
      Madeam_Exception::catchException($e, array('message' => 'See line <strong>' . $trace[3]['line'] . '</strong> in <strong>' . $trace[4]['class'] . "</strong> \n" . $error[2] . "\n" . $sql));
    }

    return $link;
  }
  
  final public function connect($connectionString) {
    try {
      // if we connect here we don't need to connect to the database until we execute a query   
      // therefore if all we're doing is loading a cached page we won't need a database connection
      if (!isset(self::$_pdo[$this->_server])) {
        // parse DB information
        $conn = $this->parseDbConnection($connectionString);
  
        // set PDO string
        $pdoString = "$conn[driver]:dbname=$conn[name];host=$conn[host]";
        
        // create database connection
        self::$_pdo[$this->_server] = new PDO($pdoString, $conn['user'], $conn['pass']);
  
        // set exception error handling
        self::$_pdo[$this->_server]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
    } catch (PDOException $e) {
      if (!isset(self::$_pdo[$this->_server]) || !is_object(self::$_pdo[$this->_server])) {
        // if the _pdo variable isn't an object it means it failed to connected
        Madeam_Exception::catchException($e, array('message' => $e->getMessage() . '. Check connection string in setup file.'));
      }
    }
    
    return true;
  }
  
  final public function fetch($sql, $prepResults = false) {
    // execute query
    $link = $this->query($sql);
    
    $results = $link->fetchAll(PDO::FETCH_ASSOC);
    $link->closeCursor();
        
    if ($prepResults === true) {
      foreach ($results as &$result) {
        // don't prepare results of describe queries
        $result = $this->prepareEntry($result);
      }
    }

    return $results;
  }

  final public function describe() {
    $table = $this->setup['resourceName'];
    return $this->fetch("DESCRIBE $table");
  }

  final public function find($fetch = 'all') {
    $data = array();

    // find _callback
    $this->_callback('beforeFind');

    // if this is a child model then filter the results to make sure they are related to this model's parent
    // this stuff is for when chaining models like:
    // $this->Srticle->findOne(32, true)->Comment->findAll();
    // where Article is the parent of Comment
    if ($this->parent && $this->_sqlWhere === false) {
      $this->where(array($this->modelName . '.' . $this->parent->setup['hasModels'][$this->modelName]['foreignKey'] => $this->parent->entry[$this->parent->setup['hasModels'][$this->modelName]['primaryKey']]));
    }
    
    // build select query and set resource link
    $link = $this->query($this->buildQuerySelect());
		
    $results = $link->fetchAll(PDO::FETCH_ASSOC);
    //$link->setFetchMode(PDO::FETCH_CLASS, get_class($this));
    //$link->setFetchMode(PDO::FETCH_OBJ);
    //$link->execute();
    //$results = $link->fetchAll();
    $link->closeCursor();
    		
		$foreignKeyValues = array();
		$belongsToForeignKeys = array();
    foreach ($results as &$result) {
      $this->_data = $result;
    	$result = $this->prepareEntry($result);    	
      $foreignKeyValues[] = $result[$this->setup['primaryKey']];      
      
      foreach ($this->setup['belongsTo'] as $model => $params) {
      	if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
      		$belongsToForeignKeys[$params['model']][] = $result[$params['foreignKey']];
    		}
    	}
    }
    
    // find related content
    if ($this->depth > 0) {
      // find hasOnes
      foreach ($this->setup['hasOne'] as $model => $params) {
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins)) && count($foreignKeyValues) > 0) {
          // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
          // clone object so we don't interupt it's state with reset()
          $tempmodel = clone $this->{$params['model']};
          $tempmodel->modelName = $params['model'];
          $hasOne = $tempmodel->where(array($params['model'] . '.' . $params['foreignKey'] => $foreignKeyValues))->findAll();
          unset($tempmodel);
          
          if ($hasOne !== false) {
            $this->_combineSingleDataOnKeys($results, $this->setup['primaryKey'], $hasOne, $params['foreignKey'], Madeam_Inflector::singalize($model));
          }
        }
      }

      // find belongsTos
      foreach ($this->setup['belongsTo'] as $model => $params) {
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins)) && count($belongsToForeignKeys) > 0) {
          // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
          // clone object so we don't interupt it's state with reset()
          $tempmodel = clone $this->{$params['model']};
          // change the model name because we can't always assume that the name will be the same.
          // An example of this is when you create a self-refrencing relationship in a table and name the relationship "sub_model" or "parent_model"
          $tempmodel->modelName = $params['model'];
          $belongTo = $tempmodel->where(array($params['model'] . '.' . $params['primaryKey'] => array_unique($belongsToForeignKeys[$params['model']])))->findAll();
          unset($tempmodel);
          
          if ($belongTo !== false) {
            $this->_combineSingleDataOnKeys($results, $params['foreignKey'], $belongTo, $params['primaryKey'], Madeam_Inflector::singalize($model)); 
          }
        }
      }

      // find hasManies
      foreach ($this->setup['hasMany'] as $model => $params) {
        // do not call if the user has not specified to call this data
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
          // clone object so we don't interupt it's state with reset()
          $tempmodel = clone $this->{$params['model']};
          $tempmodel->modelName = $params['model'];
          $hasMany = $tempmodel->where(array($params['model'] . '.' . $params['foreignKey'] => $foreignKeyValues))->findAll();
          unset($tempmodel);
          
          if ($hasMany !== false) {
            $this->_combineDataOnKeys($results, $this->setup['primaryKey'], $hasMany, $params['foreignKey'], Madeam_Inflector::pluralize($model)); 
          }
        }
      }      
      
      // find has and belongs to manies
      foreach ($this->setup['hasAndBelongsToMany'] as $model => $params) {
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
          $tempmodel = clone $this->{$params['model']}; // user model
          $habtm = $tempmodel->join($this->modelName)->where(array($params['joinModel'] . '.' . $params['foreignKey'] => $foreignKeyValues))->findAll();
          unset($tempmodel);
          
          if ($habtm !== false) {
            $this->_combineDataOnKeys($results, $this->setup['primaryKey'], $habtm, $params['foreignKey'], Madeam_Inflector::pluralize($model));
          }
        }
      }
    }

    // find _callback
    $this->_callback('afterFind');

    // grab data before it's reset
    if (!empty($results)) {
      if ($fetch != 'all') {
        $results = $results[0];
      }
    } else {
      $results = false;
    }

    // reset all sql values and data
    $this->reset();

    // return data
    return $results;
  }
  
  
  private function _combineSingleDataOnKeys(&$result1Data, $result1Key, &$result2Data, $result2Key, $combineKey) {
    foreach ($result1Data as &$entry) {
    	foreach ($result2Data as $key => &$result) {    	  
    		if ($entry[$result1Key] === $result[$result2Key]) {
    			$entry[$combineKey] = $result;
    		}
  		}
    }        
    unset($entry);
	}
  
  private function _combineDataOnKeys(&$result1Data, $result1Key, &$result2Data, $result2Key, $combineKey) {
  	foreach ($result1Data as &$entry) {
    	foreach ($result2Data as $key => &$result) {
    		if ($entry[$result1Key] === $result[$result2Key]) {
    			$entry[$combineKey][] = $result;
    			unset($result2Data[$key]);
    		}
  		}
  		unset($result);
    }        
    unset($entry);
  }

  /**
   * synonym for find()
   * @see find()
   * @return array/boolean
   */
  final public function findAll() {
    return $this->find('all');
  }

  /**
   * This is findAll except that it returns a list
   * @see find
   * @param string $value
   * @param string $label
   * @return array/boolean
   */
  final public function findList($value, $label = false) {
    $this->unbindAll();
    if ($results = $this->find()) {
      return $this->generateList($results, $value, $label);
    }
    return false;
  }

  /**
   * This turns any result into a list
   *
   * @param array $result
   * @param string $value
   * @param string $label
   * @return unknown
   */
  final public function generateList($result, $value, $label = false) {
    if ($value != null) {
      $list = array();
      if ($label == false) {
        // non-labeled list
        foreach ($result as $item) {
          $list[] = $item[$value];
        }
      } else {
        // labeled list
        foreach ($result as $item) {
          $list[$item[$label]]= $item[$value];
        }
      }
      return $list;
    }
    return false;
  }

  /**
   * Find a single row based on value of primaryKey
   *
   * @param string/int $id
   * @return array/false
   */
  final public function findOne($id = false) {
    // set where
    if ($id !== false && $this->setup['primaryKey'] !== false) {
      $this->where(array($this->modelName . '.' . $this->setup['primaryKey'] => $id));
    }

    // set limit
    $this->limit(1);

    // find stuff!
    $data = $this->find('one');

    // return data
    return $data;
  }

  /**
   * Deletes a record in the database by Primary Key value
   *
   * @param integer/string $id
   */
  final public function delete($id = false) {
    // reset all sql values and data
    $this->reset();

    // before delete _callback
    $this->_callback('beforeDelete');
    
    // set primary key value
    if ($id != false) {
      //$data[$this->setup['primaryKey']] = $id;
      $this->where(array($this->setup['primaryKey'] => $id));
    }
    
    //$affectedRows = $this->execute($this->buildQueryDelete($data));
    //final private function buildQueryDelete($data, $table = false, $where = false, $start = false, $range = false) {
    $affectedRows = $this->execute($this->buildQueryDelete($this->_data, $this->setup['resourceName'], $this->_sqlWhere, $this->_sqlStart, $this->_sqlRange));

    // after delete _callback
    $this->_callback('afterDelete');

    // check success
    if ($affectedRows > 0) {
      return $affectedRows;
    } else {
      return false;
    }
  }

  /**
   * Creates/Updates a record in the database
   *
   * An update is assumed if the primaryKey has a value
   *
   * @param array $data
   */
  final public function save($data = false) {
    // true/false
    $update = false;

    // check to see whether this is going to be an insert or an update
    $name = get_class($this);
    $inst = new $name();
    
    if ($data !== false) {
      $this->_data = $data;
    }
    
    // if the entryId exists and the record exists then it is an update. Otherwise it's an insert
    if (isset($data[$this->setup['primaryKey']]) && $data[$this->setup['primaryKey']] != false && $inst->findOne($data[$this->setup['primaryKey']])) {
      $update = true;
    }

    // unset duplicate of this model to save on sweet sweet memory
    unset($inst);

    // before Validate _callback
    $this->_callback('beforeValidate');

    // validate data
    $errors = $this->validateEntry($update);
    if (!empty($errors)) {
      throw Madeam_Model_Exception($errors);
    }


    // after Validate _callback
    $this->_callback('afterValidate');
    
    if ($update !== true) {
      $this->_callback('beforeCreate');
    }
    
    // before save _callback
    $this->_callback('beforeSave');
    
    // filter out fields that don't exist in the model
    $this->_data = array_intersect_key($this->_data, array_flip($this->setup['standardFields']));

    // if the entryId exists and the record exists then it is an update. Otherwise it's an insert
    if ($update === true) {
      $count = $this->execute($this->buildQueryUpdate($this->_data, $this->setup['resourceName'], $this->_sqlWhere, $this->_sqlStart, $this->_sqlRange, $this->setup['primaryKey']));
    } else {
      $count = $this->execute($this->buildQueryInsert($this->_data, $this->setup['resourceName'], $this->setup['primaryKey']));
    }

    // grab entry id before it's overwritten by something that happens in afterSave()
    $entryId = $this->insertId();
    
    if ($update !== true) {
      $this->_callback('afterCreate');
    }
          
    // after save _callback
    $this->_callback('afterSave');

    // reset all sql values and data
    $this->reset();

    // return data
    if ($count > 0) {
      return $count;
    }

    // failed to return any rows
    return false;
  }

  final public function habtmAdd($model, $id, $assoc = array()) {
    if (! is_array($assoc)) {
      $assoc = array($assoc);
    }

    // clone current class just because...
    // this is not cool though because this means shit is going to be validated when it shouldn't be.
    // do we need to call beforeSave and all the other _callbacks or just not worry about it?
    $class = clone $this;

    // set table name
    $class->resourceName = Madeam_Inflector::modelHabtm($model, $this->modelName);
    $class->primaryKey = false;
    $this_key = Madeam_Inflector::modelForeignKey($this->modelName);
    $relation_key = Madeam_Inflector::modelForeignKey($model);

    //$this->ItemStore->findOne(1,2);
    foreach ($assoc as $val) {
      // check for duplicate
      $results = $class->where("$this_key = '$id' AND $relation_key = '$val'")->unbindAll()->findOne();
      if (!empty($results)) {
        $class->data = array($this_key => $id, $relation_key => $val);
        $class->execute($class->buildQueryInsert());
      }
    }
    unset($class);
  }

  final public function habtmDelete($model, $id, $assoc = array()) {
    if (! is_array($assoc)) { $assoc = array($assoc); }

    // clone current class just because...
    // this is not cool though because this means shit is going to be validated when it shouldn't be.
    // do we need to call beforeSave and all the other _callbacks or just not worry about it?
    $class = clone $this;

    // set table name
    $class->resourceName = Madeam_Inflector::modelHabtm($model, $this->modelName);
    $class->primaryKey = false;
    $this_key = Madeam_Inflector::modelForeignKey($this->modelName);
    $relation_key = Madeam_Inflector::modelForeignKey($model);

    foreach ($assoc as $val) {
      $class->data = array($this_key => $id, $relation_key => $val);
      $class->where("$this_key = '$id' AND $relation_key = '$val'");
      $class->execute($class->buildQueryDelete());
    }

    unset($class);
  }

  final public function increase($id, $field, $amount = 1) {
    if ($record = $this->fields($field, $this->setup['primaryKey'])->findOne($id)) {
      $record[$field] = $record[$field] + $amount;
      if ($this->save($record)) {
        return true;
      }
    }
    return false;
  }

  final public function decrease($id, $field, $amount = 1) {
    if ($record = $this->fields($field, $this->setup['primaryKey'])->findOne($id)) {
      $record[$field] = $record[$field] - $amount;
      if ($this->save($record)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Query Builders
   * =======================================================================
   */
   
  /**
   * Create Select Query
   *
   * @return string
   */  
  final private function buildQuerySelect() {
    
    $sqlTables = array();
    $sqlFields = array();
    
    // add main table to query
    $sqlTables[] = $this->setup['resourceName'] . ' as ' . $this->modelName;
    
    // set main fields
    if (!empty($this->fields)) {
      $mainFields = array_intersect($this->fields, $this->setup['standardFields']);
    } else {
      $mainFields = $this->setup['standardFields'];
    }
    
    // get main fields
    $sqlFields[] = $this->modelName . '.' . implode(", " . $this->modelName . '.', $mainFields) . ' ';
    
    // check joins for tables and fields
    foreach ($this->_sqlJoins as $model => $join) {
      if (!isset($this->setup['hasAndBelongsToMany'][$model])) {
        $sqlTables[] = 'LEFT JOIN ' . $join['table'] . ' as ' . $join['model'] . ' on ' . $join['on'];
        
        //$selectFields = $this->$model->getFields();
        $selectFields = $this->$model->fields;
        if (!empty($selectFields)) {
          $modelFields = array_intersect($selectFields, $this->$model->setup['standardFields']);
        } else {
          $modelFields = $this->$model->setup['standardFields'];
        }
      
        $sqlFields[] = $model . '.' . implode(", " . $model . '.', $this->$model->setup['standardFields']);
      } else {
        $sqlTables[] = 'LEFT JOIN ' . $join['table'] . ' as ' . $this->setup['hasAndBelongsToMany'][$model]['joinModel'] . ' on ' . $join['on'];
        $sqlFields[] = $this->setup['hasAndBelongsToMany'][$model]['joinModel'] . '.' . $this->setup['hasAndBelongsToMany'][$model]['foreignKey'] . ', ' . $this->setup['hasAndBelongsToMany'][$model]['joinModel'] . '.' . $this->setup['hasAndBelongsToMany'][$model]['associateForeignKey'];
      }
    }
    
    $sql[] = 'SELECT ' . implode(', ', $sqlFields) . ' FROM ' . implode(' ', $sqlTables);    
    
    // add where
    if ($this->_sqlWhere === false) {
      $sql[] = 'WHERE 1';
    } else {
      $sql[] = 'WHERE ' . $this->_sqlWhere;
    }
    
    // add order
    if ($this->_sqlOrder) {
      $sql[] = 'ORDER BY ' . $this->_sqlOrder;
    }
    
    // add limit
    if ($this->_sqlStart !== false && $this->_sqlRange !== false) {
      $sql[] = "LIMIT $this->_sqlStart, $this->_sqlRange";
    } elseif ($this->_sqlStart !== false && $this->_sqlRange === false) {
      $sql[] = "LIMIT $this->_sqlStart";
    }

    return implode(' ', $sql) . ';';
  }


  /**
   * Create Insert Query
   *
   * @return string
   */
  final private function buildQueryInsert($data, $table = false, $primaryKey = false) {
    
    // remove primary key insert if it is null
    if (!isset($data[$primaryKey]) || $data[$primaryKey] == null) {
      unset($data[$primaryKey]);
    }
    
    $sql = array();
    
    // add table name
    $sql[] = 'INSERT INTO ' . $table;
    // add fields
    $sql[] = '(' . implode(',', array_keys($data)) . ')';
    // close fields, open values
    $sql[] = 'VALUES';
    // add values
    $sql[] = "('" . @implode("','", array_values($data)) . "')";
    
    // build query
    return implode(' ', $sql) . ';';
  }

  /**
   * Create Update Query
   *
   * @return string
   */
  final private function buildQueryUpdate($data, $table = false, $where = false, $start = false, $range = false, $primaryKey = false) {
    $sql = array();
    
    // add table name
    $sql[] = 'UPDATE ' . $table . ' SET';
    
    // fields
    if (empty($this->fields)) {
      $sets = array();
      foreach ($data as $field => $value) {
        $sets[] = "$field = " . Madeam_Sanitize::escape($value);
      }
    } else {
      foreach ($this->fields as $field) {
        $sets[] = "$field = " . Madeam_Sanitize::escape($data[$field]);        
      }
    }
    
    // add fields
    $sql[] = implode(", \n", $sets);
    
    // add where condition
    if ($where != false) {
      $sql[] = 'WHERE ' . $where;
    } elseif ($this->entryId != - 1) {
      $sql[] = 'WHERE ' . $primaryKey . ' = ' . Madeam_Sanitize::escape($data[$primaryKey]);
    }
    
    // add limit
    if ($start && $range) {
      $sql[] = "LIMIT $start, $range";
    } elseif ($start && ! $range) {
      $sql[] = "LIMIT $start";
    } else {
      $sql[] = 'LIMIT 1';
    }

    // build query
    return implode(' ', $sql) . ';';
  }

  /**
   * Create Delete Query
   *
   * @return string
   */
  final private function buildQueryDelete($data, $table = false, $where = false, $start = false, $range = false) {
    $sql = array();
    
    // add table name
    $sql[] = 'DELETE FROM ' . $table;
    
    // add where condition
    if ($where != 1) {
      $sql[] = 'WHERE ' . $where;
    } elseif ($this->entryId != - 1) {
      $sql[] = 'WHERE ' . $primaryKey . ' = ' . Madeam_Sanitize::escape($data[$primaryKey]);
    } else {
      return false;
    }
    
    // add limit
    if ($start && $range) {
      $sql[] = "LIMIT $start, $range";
    } elseif ($start && ! $range) {
      $sql[] = "LIMIT $start";
    } else {
      $sql[] = 'LIMIT 1';
    }

    return implode(' ', $sql) . ';';
  }

  /**
   * Query Modifiers
   * =======================================================================
   */
   
  /**
   * Creates WHERE statement
   *
   * @param array $conditions
   */
  final public function where() {
  	$conditions = func_get_args();
  	$condition = $this->_conditions($conditions);
  	
  	if ($this->_sqlWhere != false) {
  		$this->_sqlWhere .= ' and ' . $condition;
		} else {
			$this->_sqlWhere = $condition;
		}
		
  	return $this;
  }
  
  
  /**
	 * Generates sql conditions
	 * Note - should try using pdo's prepared statements instead of escaping input 
	 * with the quote method
	 * 
	 * @return string
	 */
  private function _conditions($conditions) {
  	$condition = null;
  	
  	foreach ($conditions as $field => $value) {	
  		if (is_array($value) && is_integer($field)) {
  			$condition .= ' (' . $this->_conditions($value) . ') ';
			} elseif (is_integer($field) && in_array($value, array('or', 'and', 'xor'))) {
				// this isn't a condition but an operator joining conditions
				$condition .= ' ' . $value . ' ';
			} elseif (is_integer($field)) {
				// when the field is an integer and none of the above conditions are met we assume
				// that this condition is written by the user and doesn't need madeam's help
				$condition .= ' (' . $value . ') ';
  		} else {
  			$x = strstr($field, ' ');
  			if ($x == null && !is_array($value)) {
  				$condition .= $field . ' = ';
				} else {
					$condition .= $field . ' ';
				}
				
				if (is_array($value)) {
					if (!empty($value)) {
						// when the value is an array we assume the user is trying to do an "IN" comparison
						$condition .= ' in (' . implode(',', $value) . ')';
					}
				} else {
					// if the value didn't match any of the above conditions then it must be a string
					// and therefore needs quotes
					$condition .= Madeam_Sanitize::escape($value);
				}
  		}
  	}
  	
  	return $condition;
  }

  /**
   * Set the order of the sql query
   *
   * @param string $order
   */
  final public function order($order) {
    $this->_sqlOrder = $order;
    return $this;
  }

  /**
   * Set limit of query
   *
   * @param int $start
   * @param int $range
   */
  final public function limit($start, $range = false) {
    $this->_sqlStart = $start;
    $this->_sqlRange = $range;
    return $this;
  }

  /**
   * Sets the fields to be selected, automatically includes primaryKey
   *
   * @param list $fields
   */
  final public function fields($fields) {
    if (!is_array($fields)) {
      $fields = func_get_args();
    }

    foreach ($fields as $field) {
      if (!in_array($field, $this->setup['customFields'])) {
        $this->_sqlFields[] = $field;
      }
      $this->fields[] = $field;
    }
    
    // make sure there are no dupes
    $this->fields = $this->_sqlFields = array_unique($this->fields);
    return $this;
  }

  /**
   * Set the depth at which the active_record will search for relationships
   *
   * @param int $size
   */
  final public function depth($size) {
    $this->depth = $size;
    return $this;
  }

  final public function join($model, $on = false) {
    $model = Madeam_Inflector::modelNameize($model);
    $table = $this->setup['resourceName'];
    if ($on === false) {
      if (in_array($model, array_keys($this->setup['hasAndBelongsToMany']))) {
        // get relation config
        $relation = $this->setup['hasAndBelongsToMany'][$model];

        // set on condition
        $on = $this->modelName . '.' . $this->setup['primaryKey'] . ' = ' . $relation['joinModel'] . '.' . $relation['foreignKey'];

        // add join
        $this->_sqlJoins[$model] = array('table' => $relation['joinResourceName'], 'on' => $on);
      } elseif (in_array($model, array_keys(array_merge($this->setup['hasOne'], $this->setup['hasMany'], $this->setup['belongsTo'])))) {
        $fk = $this->setup['hasModels'][$model]['foreignKey'];
        // I wish there was a better way to do this...
        // make the user type in more info?
        $foreignTable = $this->$model->setup['resourceName'];
        $foreignPk = $this->$model->setup['primaryKey'];
        $on = "$table.$fk = $foreignTable.$foreignPk";
        $this->_sqlJoins[$model] = array('table' => $this->$model->setup['resourceName'], 'on' => $on);
      } else {
        $this->_sqlJoins[$model] = array('table' => $table, 'on' => $on);
      }
    } else {
      $this->_sqlJoins[$model] = array('table' => $this->$model->setup['resourceName'], 'on' => $on);
    }
    return $this;
  }

  /**
   * Resets all the sql values and data
   */
  final public function reset() {
    $this->_sqlWhere = false;
    $this->_sqlOrder = false;
    $this->_sqlStart = false;
    $this->_sqlRange = false;
    $this->_sqlGroup = false;
    $this->_sqlFields = array();
    $this->_sqlJoins = array();
    $this->fields = array();
    $this->entryId = - 1;
    $this->depth = 1;
    $this->unbound = array();
    
    // reset child models
    // this is so we can do stuff like...
    // $this->article->comment->depth(2)->fields('id', 'title');
    // $article = $this->article->findOne(32);
    // which will return an article with all of it's comments defined by it's sql modifiers like depth() and fields() and others
    foreach ($this->setup['hasModels'] as $model => $info) {
      if (isset($this->$model)) {
        $this->$model->reset();
      }
    }
  }


  /**
   * Gets ID of last row inserted
   *
   * @return integer
   */
  final public function insertId() {
    // this is necessary when the programmer has to specify a primaryKey value when the
    // primaryKey does not auto increment or is not an integer
    if ($this->entryId != - 1) {
      return $this->entryId;
    } else {
      return self::$_pdo[$this->_server]->lastInsertId();
    }
  }

  /**
   * Return number of rows affected
   *
   * @return int
   */
  final public function affectedRows() {
    return self::$_pdo[$this->_server]->rowCount();
  }

  
  

  final public function parseDbConnection($string) {
    $details = array();
    
    // parse connection string as url
    $parsed_string = parse_url($string);
    isset($parsed_string['scheme']) ? $details['driver'] = $parsed_string['scheme'] : $details['driver'] = null;
    isset($parsed_string['host']) ? $details['host'] = $parsed_string['host'] : $details['host'] = null;
    isset($parsed_string['user']) ? $details['user'] = $parsed_string['user'] : $details['user'] = null;
    isset($parsed_string['pass']) ? $details['pass'] = $parsed_string['pass'] : $details['pass'] = null;
    
    parse_str($parsed_string['query'], $options);
    isset($options['name']) ? $details['name'] = $options['name'] : $details['name'] = null;
    isset($options['port']) ? $details['port'] = $options['port'] : $details['port'] = false;
    
    return $details;
  }

}
