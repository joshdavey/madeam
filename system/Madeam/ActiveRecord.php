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
class Madeam_ActiveRecord extends Madeam_Model {

  /**
   * Enter description here...
   *
   * @var array
   */
  protected $data = array();

  /**
   * Represents a single row
   *
   * @var array
   */
  protected $entry = array();

  /**
   * Enter description here...
   *
   * @var string
   */
  public $sql = null;

  /**
   * Query resource link
   *
   * @var resource
   */
  protected $link = null;

  /**
   * Entry Identifier
   *
   * @var mixed
   */
  protected $entryId = - 1;

  /**
   * Enter description here...
   *
   * @var boolean
   */
  protected $isInsert = false;

  /**
   * Enter description here...
   *
   * @var boolean
   */
  protected $isUpdate = false;

  /**
   * The name of the server configuration we want to use.
   *
   * @var string/integer
   */
  protected $server = 0;

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
  private $_sqlWhere = '1';

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
      $this->where(array($this->name . '.' . $match[2] => $args[0]));
      $function = 'find' . $match[1];
      if (method_exists($this, $function)) {
        return $this->$function();
      }
    } elseif (preg_match("/^deleteBy_(.*)/", $name, $match)) {
      $this->where(array($this->name . '.' . $match[2] => $args[0]));
      $this->delete();
    } else {
      $backtrace = debug_backtrace();
      throw new Madeam_Exception_MissingMethod("See line <strong>" . $backtrace[1]['line'] . "</strong> in <strong>" . $backtrace[1]['file'] . "</strong> \n Unknown method " . $name . ' in ' . get_class($this) . " model.");
    }

    return false;
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
      try {
        // if we connect here we don't need to connect to the database until we execute a query
        // therefore if all we're doing is loading a cached page we won't need a database connection
        if (!isset(self::$_pdo[$this->server])) {
          // parse DB information
          $servers = Madeam_Config::get('data_servers');
          $serverConnectionString = $servers[$this->server];
          $server = $this->parseDbConnection($serverConnectionString);

          // set PDO string
          $pdoString = "$server[driver]:dbname=$server[name];host=$server[host]";
          
          // create database connection
          self::$_pdo[$this->server] = new PDO($pdoString, $server['user'], $server['pass']);

          // set exception error handling
          self::$_pdo[$this->server]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // for debugging only
        $this->sql = $sql;

        // log -- I hate using Madeam_Logger for this stuff... can't we catch it all somewhere?
        Madeam_Logger::getInstance()->log($sql);

        // link
        $result = self::$_pdo[$this->server]->query($sql, true);

      } catch (PDOException $e) {
        if (!isset(self::$_pdo[$this->server]) || !is_object(self::$_pdo[$this->server])) {
          // if the _pdo variable isn't an object it means it failed to connected
          Madeam_Exception::catchException($e, array('message' => $e->getMessage() . '. Check connection string in setup file.'));
        } else {
          $trace = $e->getTrace();
          $error = self::$_pdo[$this->server]->errorInfo();
          Madeam_Exception::catchException($e, array('message' => 'See line <strong>' . $trace[3]['line'] . '</strong> in <strong>' . $trace[4]['class'] . "</strong> \n" . $error[2] . "\n" . $sql));
        }
      }

    return $result;
  }

  final public function query($sql, $prepResults = false) {
    // execute query
    $link = $this->execute($sql);
    
    // check to see if this query returns a resource -- why not just check to see if it's a resource instead?
    $matchs = array();
    preg_match('/^DESCRIBE|SELECT/', $sql, $matchs);
    if (count($matchs) > 0) {
      $results = $link->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $result) {
        $this->entry = $result;
        // don't prepare results of describe queries
        if ($prepResults === true) {
          $this->prepareResult(); // should this be done?
        }

        $this->data[] = $this->entry;
      }

      return $this->data;
    } else {
      return $result;
    }
  }

  final public function describe() {
    $table = $this->setup['resourceName'];
    return $this->query("DESCRIBE $table");
  }

  final public function find($fetch = 'all') {
    $this->data = array();

    // find _callback
    $this->_callback('beforeFind');

    // if this is a child model then filter the results to make sure they are related to this model's parent
    // this stuff is for when chaining models like:
    // $this->Srticle->findOne(32, true)->Comment->findAll();
    // where Article is the parent of Comment
    if ($this->parent && $this->_sqlWhere == '1') {
      $this->where(array($this->name . '.' . $this->parent->setup['hasModels'][$this->name]['foreignKey'] => $this->parent->entry[$this->parent->setup['hasModels'][$this->name]['primaryKey']]));
    }
		
    // build select query and set resource link
    $link = $this->execute($this->buildQuerySelect());
		
    $results = $link->fetchAll(PDO::FETCH_ASSOC);
		
		$foreignKeyValues = array();
		$belongsToForeignKeys = array();
    foreach ($results as $result) {
    	$this->entry = $this->prepareEntry($result);
      $this->data[] = $this->entry;
      $foreignKeyValues[] = $this->entry[$this->setup['primaryKey']];      
      
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
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
          // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
          // clone object so we don't interupt it's state with reset()
          $tempmodel = clone $this->{$params['model']};
          $tempmodel->name = $params['model'];
          $hasOne = $tempmodel->where(array($params['model'] . '.' . $params['foreignKey'] => $foreignKeyValues))->findAll();
          unset($tempmodel);
          
          $this->_combineSingleDataOnKeys($this->data, $this->setup['primaryKey'], $hasOne, $params['foreignKey'], Madeam_Inflector::singalize($model));
        }
      }

      // find belongsTos
      foreach ($this->setup['belongsTo'] as $model => $params) {
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
          // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
          // clone object so we don't interupt it's state with reset()
          $tempmodel = clone $this->{$params['model']};
          // change the model name because we can't always assume that the name will be the same.
          // An example of this is when you create a self-refrencing relationship in a table and name the relationship "sub_model" or "parent_model"
          $tempmodel->name = $params['model'];
          $belongTo = $tempmodel->where(array($params['model'] . '.' . $params['primaryKey'] => array_unique($belongsToForeignKeys[$params['model']])))->findAll();
          unset($tempmodel);
          
          $this->_combineSingleDataOnKeys($this->data, $params['foreignKey'], $belongTo, $params['primaryKey'], Madeam_Inflector::singalize($model)); 
        }
      }

      // find hasManies
      foreach ($this->setup['hasMany'] as $model => $params) {
        // do not call if the user has not specified to call this data
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
          // clone object so we don't interupt it's state with reset()
          $tempmodel = clone $this->{$params['model']};
          $tempmodel->name = $params['model'];
          $hasMany = $tempmodel->where(array($params['model'] . '.' . $params['foreignKey'] => $foreignKeyValues))->findAll();
          unset($tempmodel);
            
          $this->_combineDataOnKeys($this->data, $this->setup['primaryKey'], $hasMany, $params['foreignKey'], Madeam_Inflector::pluralize($model)); 
        }
      }      
      
      // find has and belongs to manies
      foreach ($this->setup['hasAndBelongsToMany'] as $model => $params) {
        if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
          $tempmodel = clone $this->{$params['model']}; // user model
          $habtm = $tempmodel->join($this->name)->where(array($params['joinModel'] . '.' . $params['foreignKey'] => $foreignKeyValues))->findAll();
          unset($tempmodel);
          
          $this->_combineDataOnKeys($this->data, $this->setup['primaryKey'], $habtm, $params['foreignKey'], Madeam_Inflector::pluralize($model));
        }
      }
    }

    // find _callback
    $this->_callback('afterFind');

    // grab data before it's reset
    if ($fetch == 'all') {
      $data = $this->data;
    } else {
      $data = $this->entry = $this->data[0];
    }

    // reset all sql values and data
    $this->reset();

    // return data
    return $data;
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
          $list[] = array($item[$label], $item[$value]);
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
  final public function findOne($id = -1, $chain = false) {
    $this->entry = array();

    // set entryId
    if ($id != - 1) {
      $this->entryId = $id;
    }

    // set where
    if ($this->entryId != - 1 && $this->setup['primaryKey'] !== false) {
      $this->where(array($this->name . '.' . $this->setup['primaryKey'] => $id));
    }

    // set limit
    $this->limit(1);

    // find stuff!
    $data = $this->find('one');

    if ($chain) {
      // return object because this entry is being chained
      return $this;
    } else {
      return $data;
    }
  }

  /**
   * Deletes a record in the database by Primary Key value
   *
   * @param integer/string $id
   */
  final public function delete($id = -1) {
    // reset all sql values and data
    $this->reset();

    // Validate _callbacks
    $this->_callback('beforeValidate');
    $this->_callback('afterValidate');

    // before delete _callback
    $this->_callback('beforeDelete');
    if ($id != - 1) {
      $this->entryId = $id;
    }

    $this->execute($this->buildQueryDelete());

    // after delete _callback
    $this->_callback('afterDelete');

    // check success
    if ($this->affectedRows() > 0) {
      return true;
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
  final public function save($data) {
    // true/false
    $update = false;

    // set $this->entry so it is accessible in _callbacks
    $this->entry = $data;

    // set entryId
    if (isset($this->entry[$this->setup['primaryKey']]) && $this->entry[$this->setup['primaryKey']] != null) {
      $this->entryId = $this->entry[$this->setup['primaryKey']];
    }

    // check to see whether this is going to be an insert or an update
    $name = get_class($this);
    $inst = new $name();

    // if the entryId exists and the record exists then it is an update. Otherwise it's an insert
    if ($this->entryId != - 1 && $inst->findOne($this->entryId)) {
      $update = true;
    }

    // unset duplicate of this model to save on sweet sweet memory
    unset($inst);

    // before Validate _callback
    $this->_callback('beforeValidate');

    // validate data
    ////$this->load_validators();
    $this->validateEntry($update);

    // after Validate _callback
    $this->_callback('afterValidate');
    // now that all the _callbacks prior to updating/adding the new row have been called
    // we must check for any errors that may have been envoked.
    // if there aren't any then continue as usual.
    // if not then skip adding/updating
    if (! isset($_SESSION[Madeam::user_error_name]) || count($_SESSION[Madeam::user_error_name]) < 1) {
      // do standard field formats
      $this->standardFieldFormats();

      // before save _callback
      $this->_callback('beforeSave');
      /*
      // relations
      $relations = array();

      // determine if any of the fields represents any of this model's relationships
      foreach ($this->entry as $field => $value) {
        if (is_array($value)) {
          $relations[$field] = $value;

          // instead of just unsetting it, it should get it's primaryKey value!
          $this->entry[$field] = $value[$this->{Madeam_Inflector::modelNameize($field)}->primaryKey];
        }
      }
      */
      // set data as row after _callbacks have altered $this->entry
      $this->data = $this->entry;

      // filter out fields that don't exist in the model
      $this->data = array_intersect_key($this->data, array_flip($this->setup['standardFields']));

      // if the entryId exists and the record exists then it is an update. Otherwise it's an insert
      if ($update === true) {
        $this->isUpdate = true; // can be used by the dev to figure out if it's an insert or update when using _callbacks
        $this->execute($this->buildQueryUpdate());
      } else {
        $this->isInsert = true; // can be used by the dev to figure out if it's an insert or update when using _callbacks
        $this->execute($this->buildQueryInsert());
      }

      // grab entry id before it's overwritten by something that happens in afterSave()
      $entryId = $this->insertId();

      // set this so it can be used in afterSave
      $this->entry[$this->setup['primaryKey']] = $entryId;

      // grab entry after it's been modified by _callbacks
      $entry = $this->entry;
      /*
      if ($entryId) {
        // check for fields that are arrays
        foreach ($relations as $model => $value) {
          $model = Madeam_Inflector::modelNameize($model);
          if (in_array($model, array_keys($this->setup['hasAndBelongsToMany']))) {
            $this->habtmAdd($model, $entryId, $value);
          } else {
            $this->$model->save($value);
          }
        }
      }
      */
      // after save _callback
      $this->_callback('afterSave');

      // reset all sql values and data
      $this->reset();

      return $entry;
    } else {
      // reset all sql values and data
      $this->reset();
      return false;
    }

    // reset all sql values and data
    $this->reset();

    // return data
    if ($this->affectedRows() > 0) {
      return $this->data;
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
    $class->resourceName = Madeam_Inflector::modelHabtm($model, $this->name);
    $class->primaryKey = false;
    $this_key = Madeam_Inflector::modelForeignKey($this->name);
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
    $class->resourceName = Madeam_Inflector::modelHabtm($model, $this->name);
    $class->primaryKey = false;
    $this_key = Madeam_Inflector::modelForeignKey($this->name);
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
  	$sqlTables[] = $this->setup['resourceName'] . ' as ' . $this->name;
  	
  	// set main fields
  	if (!empty($this->fields)) {
  		$mainFields = array_intersect($this->fields, $this->setup['standardFields']);
		} else {
			$mainFields = $this->setup['standardFields'];
		}
  	
  	// get main fields
  	$sqlFields[] = $this->name . '.' . implode(", " . $this->name . '.', $mainFields) . ' ';
  	
  	// check joins for tables and fields
  	foreach ($this->_sqlJoins as $model => $join) {
    	if (!isset($this->setup['hasAndBelongsToMany'][$model])) {
    		$sqlTables[] = 'LEFT JOIN ' . $join['table'] . ' as ' . $join['model'] . ' on ' . $join['on'];
    		
    		$selectFields = $this->$model->getFields();
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
    $sql[] = 'WHERE ' . $this->_sqlWhere;
    
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
  final private function buildQueryInsert() {
    $sql = array();
    $table = $this->setup['resourceName'];
    // remove primary key insert if it is null
    if (! isset($this->data[$this->setup['primaryKey']]) || $this->data[$this->setup['primaryKey']] == null) {
      unset($this->data[$this->setup['primaryKey']]);
    }
    // add table name
    $sql[] = 'INSERT INTO ' . $table;
    // add fields
    $sql[] = '(' . implode(',', array_keys($this->data)) . ')';
    // close fields, open values
    $sql[] = 'VALUES';
    // add values
    $sql[] = "('" . @implode("','", array_values($this->data)) . "')";
    // build query

    return implode(' ', $sql) . ';';
  }

  /**
   * Create Update Query
   *
   * @return string
   */
  final private function buildQueryUpdate() {
    $sql = array();
    $table = $this->setup['resourceName'];
    // add table name
    $sql[] = 'UPDATE ' . $table . ' SET';
    // fields
    if (empty($this->fields)) {
      $sets = array();
      foreach ($this->data as $field => $value) {
        $sets[] = "$field = " . Madeam_Sanitize::escape($value);
      }
    } else {
      foreach ($this->fields as $field) {
        $sets[] = "$field = " . Madeam_Sanitize::escape($this->data[$field]);        
      }
    }
    // add fields
    $sql[] = implode(", \n", $sets);
    // add where condition
    if ($this->_sqlWhere != 1) {
      $sql[] = 'WHERE ' . $this->_sqlWhere;
    } elseif ($this->entryId != - 1) {
      $sql[] = 'WHERE ' . $this->setup['primaryKey'] . ' = ' . Madeam_Sanitize::escape($this->entryId);
    } else {
      return false;
    }
    // add limit
    if ($this->_sqlStart && $this->_sqlRange) {
      $sql[] = "LIMIT $this->start, $this->_sqlRange";
    } elseif ($this->_sqlStart && ! $this->_sqlRange) {
      $sql[] = "LIMIT $this->_sqlStart";
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
  final private function buildQueryDelete() {
    $sql = array();
    // add table name
    $sql[] = 'DELETE FROM ' . $this->setup['resourceName'];
    // add where condition
    if ($this->_sqlWhere != 1) {
      $sql[] = 'WHERE ' . $this->_sqlWhere;
    } elseif ($this->entryId != - 1) {
      $sql[] = 'WHERE ' . $this->setup['primaryKey'] . ' = ' . Madeam_Sanitize::escape($this->entryId);
    } else {
      return false;
    }
    // add limit
    if ($this->_sqlStart && $this->_sqlRange) {
      $sql[] = "LIMIT $this->start, $this->_sqlRange";
    } elseif ($this->_sqlStart && ! $this->_sqlRange) {
      $sql[] = "LIMIT $this->_sqlStart";
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
  	
  	if ($this->_sqlWhere != 1) {
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
        $on = $this->name . '.' . $this->setup['primaryKey'] . ' = ' . $relation['joinModel'] . '.' . $relation['foreignKey'];

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
    $this->_sqlWhere = '1';
    $this->_sqlOrder = false;
    $this->_sqlStart = false;
    $this->_sqlRange = false;
    $this->_sqlGroup = false;
    $this->_sqlFields = array();
    $this->_sqlJoins = array();
    $this->fields = array();
    $this->data = array();
    $this->entry = array();
    $this->entryId = - 1;
    $this->depth = 1;
    $this->isInsert = false;
    $this->isUpdate = false;
    $this->unbound = array();
    //$this->sql = null;
    
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
      return self::$_pdo[$this->server]->lastInsertId();
    }
  }

  /**
   * Return number of rows affected
   *
   * @return int
   */
  final public function affectedRows() {
    return self::$_pdo[$this->server]->rowCount();
  }

  /**
   * Getters
   * =======================================================================
   */
  final public function getLabel() {
    return $this->label;
  }

  /**
   * Lame functions I want to remove
   * =======================================================================
   */
  /**
   * This should be a global function some day.
   *
   * @param unknown_type $date
   * @return unknown
   */
  final protected function formatDatetime($date = array()) {
    if (is_array($date)) {
      //2006-12-26 19:26:19
      $date['year'] ? true : $date['year'] = '0000';
      $date['month'] ? true : $date['month'] = '00';
      $date['day'] ? true : $date['day'] = '00';
      $date['hour'] ? true : $date['hour'] = '00';
      $date['minute'] ? true : $date['minute'] = '00';
      $date['second'] ? true : $date['second'] = '00';
      return "$date[year]-$date[month]-$date[day] $date[hour]:$date[minute]:$date[second]";
    } else {
      return $date;
    }
  }

  final protected function formatDatetimeField($field, $default) {
    if (isset($this->entry[$field])) {
      if (is_string($this->entry[$field]) && $this->entry[$field] == null) {
        $this->entry[$field] = date($default);
      } else {
        $this->entry[$field] = $this->formatDatetime($this->entry[$field]);
      }
    }
  }

  final protected function standardFieldFormats() {
    // created_on
    $this->formatDatetimeField('created_on', 'Y-m-d 00:00:00');
    // created_at
    $this->formatDatetimeField('created_at', 'Y-m-d H:i:s');
    // updated_on
    $this->formatDatetimeField('updated_on', 'Y-m-d 00:00:00');
    // updated_at
    $this->formatDatetimeField('updated_at', 'Y-m-d H:i:s');
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
