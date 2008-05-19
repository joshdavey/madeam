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
   * Name of field that acts as label for a row
   *
   * @var string
   */
  protected $label = null;


  /**
   * Connection Resource
   *
   * @var resource/boolean
   */
  protected $conn = false;

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
   * Enter description here...
   *
   * @var boolean
   */
  private $_sqlExplain = false;

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
      $this->where($match[2] . " = '$args[0]'");
      $function = 'find' . $match[1];
      if (method_exists($this, $function)) {
        return $this->$function();
      }
    } /*elseif (preg_match("/^deleteBy_(.*)/", $name, $match)) {
      $this->where($match[2] . " = '$args[0]'");
      $this->delete();
    }*/
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
  final public function execute($sql, $returnAs = 'array') {

      try {

        // if we connect here we don't need to connect to the database until we execute a query
        // therefore if all we're doing is loading a cached page we won't need a database connection
        if (!isset($_GLOBAL['databaseConnection']) || !is_resource($_GLOBAL['databasConnection'])) {
          // parse DB information
          $config = Madeam_Registry::get('config');
          $servers = $config['data_servers'];
          $serverConnectionString = $servers[$this->server];
          $server = $this->parseDbConnection($serverConnectionString);

          // create database connection
          if (isset($server['port']) && $server['port'] != false) {
            $_GLOBAL['databaseConnection'] = mysql_connect($server['host'] . ':' . $server['port'], $server['user'], $server['pass']);
            if ($_GLOBAL['databaseConnection'] === false) {
              throw new Madeam_Exception_ConnectionFail(mysql_error());
            }
          } else {
            $_GLOBAL['databaseConnection'] = mysql_connect($server['host'], $server['user'], $server['pass']);
            if ($_GLOBAL['databaseConnection'] === false) {
              throw new Madeam_Exception_ConnectionFail(mysql_error());
            }
          }
        }

        // select database
        if (! mysql_select_db($server['name'], $_GLOBAL['databaseConnection'])) {
          // failed to find selected database
          throw new Madeam_Exception_MissingResource(mysql_error());
        }

        // for debugging only
        $this->sql = $sql;

        // log
        Madeam_Logger::log($sql);

        // execute mysql query
        $this->link = mysql_query($sql, $_GLOBAL['databaseConnection']);

        // check for errors
        if (mysql_error()) {
          throw new Madeam_Exception_QueryFail(null, 2);
        }

      } catch (Madeam_Exception_ConnectionFail $e) {
        $e->setMessage(mysql_error() . '. Check connection string in setup file.');
        Madeam_Error::catchException($e);
      } catch (Madeam_Exception_MissingResource $e) {
        $e->setMessage(mysql_error() . '. Check connection string in setup file.');
        Madeam_Error::catchException($e);
      } catch (Madeam_Exception_QueryFail $e) {
        $trace = $e->getTrace();
        $e->setMessage('See line <strong>' . $trace[2]['line'] . '</strong> in <strong>' . $trace[3]['class'] . "</strong> \n" . mysql_error() . "\n" . $sql);
        Madeam_Error::catchException($e);
      }

    return $this->link;
  }

  final public function query($sql, $returnAs = 'array') {
    // execute query
    $this->link = $this->execute($sql);

    // fetch method
    if ($returnAs == 'object') {
      $fetchMethod = 'mysql_fetch_object';
    } else {
      $fetchMethod = 'mysql_fetch_assoc';
    }

    // check to see if this query returns a resource -- why not just check to see if it's a resource instead?
    $matchs = array();
    preg_match('/^DESCRIBE|SELECT/', $sql, $matchs);
    if (count($matchs) > 0) {
      if ($this->numRows() > 0) {
        while($this->entry = $fetchMethod($this->link)) {
          // don't prepare results of describe queries
          if ($matchs[0] != "DESCRIBE" && $matchs[0] != 'describe') {
            $this->prepareResult(); // should this be done?
          }

          $this->data[] = $this->entry;
        }

        return $this->data;
      } else {
        return array(); // return empty data
      }
    } else {
      return $this->link;
    }
  }

  final public function describe() {
    $table = $this->setup['resourceName'];
    return $this->query("DESCRIBE $table", 'array');
  }

  /**
   * Returns many rows. The query generated by findAll can be tweaked with the methods below
   * @see where()
   * @see limit()
   * @see order()
   * @see fields()
   *
   * @return array/boolean
   */
  final public function find() {
    $this->data = array();

    // find callback
    $this->beforeFind();

    // if this is a child model then filter the results to make sure they are related to this model's parent
    // this stuff is for when chaining models like:
    // $this->article->findOne(32, true)->comment->findAll();
    // where article is the parent of comment
    if ($this->parent && $this->_sqlWhere == '1') {
      $this->where($this->parent->setup['hasModels'][$this->name]['foreignKey'] . " = '" . $this->parent->entry[$this->parent->setup['hasModels'][$this->name]['primaryKey']] . "'");
      //$this->where($this->parent->setup['hasModels'][$this->name]['foreignKey'] . " = '" . $this->parent->setup['hasModels'][$this->name]['primaryKey'] . "'");
    }

    // build select query and set resource link
    $this->link = $this->execute($this->buildQuerySelect());
    if (is_resource($this->link)) {
      // get data
      //while ($this->entry = mysql_fetch_object($this->link)) {
      while($this->entry = mysql_fetch_assoc($this->link)) {
        // adds custom fields
        $this->prepareResults();

        // find related content
        if ($this->depth > 0) {
          // find has_ones
          foreach ($this->setup['hasOne'] as $model => $params) {
            $fkey = $params['foreignKey'];
            if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
              // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
              // clone object so we don't interupt it's state with reset()
              $tempmodel = clone $this->{$params['model']};
              $tempmodel->name = $model;
              $this->entry[$model] = $tempmodel->findOne($this->entry[$fkey]);
              unset($tempmodel);
            }
          }

          // find belongsTos
          foreach ($this->setup['belongsTo'] as $model => $params) {
            $fkey = $params['foreignKey'];
            if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
              // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
              // clone object so we don't interupt it's state with reset()
              $tempmodel = clone $this->{$params['model']};
              // change the model name because we can't always assume that the name will be the same.
              // An example of this is when you create a self-refrencing relationship in a table and name the relationship "sub_model" or "parent_model"
              $tempmodel->name = $model;
              $this->entry[$model] = $tempmodel->findOne($this->entry[$fkey]);
              unset($tempmodel);
            }
          }

          // find hasManies
          foreach ($this->setup['hasMany'] as $model => $params) {
            // do not call if the user has not specified to call this data
            if (! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
              // clone object so we don't interupt it's state with reset()
              $tempmodel = clone $this->{$params['model']};
              $tempmodel->name = $model;
              $this->entry[Madeam_Inflector::pluralize($model)] = $tempmodel->findAll();
              unset($tempmodel);
            }
          }

          // find has and belongs to manies
          foreach ($this->setup['hasAndBelongsToMany'] as $model => $params) {
            if ((in_array($model, $this->fields) || empty($this->fields)) && ! in_array($model, array_values($this->unbound)) && ! in_array($model, array_keys($this->_sqlJoins))) {
              $tempmodel = clone $this->{$params['model']};
              $tempmodel->name = $model;
              $this->entry[Madeam_Inflector::pluralize($params['model'])] = $tempmodel->join($this->name)->findAll();
              unset($tempmodel);
            }
          }
        }
        $this->data[] = $this->entry;
      }
    }

    // find callback
    $this->afterFind();

    // return data
    if ($this->numRows() > 0) {
      // grab data before it's reset
      $data = $this->data;

      // reset all sql values and data
      $this->reset();
      return $data;
    }

    // reset all sql values and data
    $this->reset();

    // failed to return any rows
    return false;
  }

  /**
   * synonym for find()
   * @see find()
   * @return array/boolean
   */
  final public function findAll() {
    return $this->find();
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
    if ($this->entryId != - 1 && $this->primaryKey != false) {
      $table = $this->setup['resourceName'];
      $this->where("$table.$this->primaryKey = '$id'");
    }

    // set limit
    $this->limit(1);

    // find stuff!
    $this->data = $this->find();
    $this->entry = $this->data[0];

    if ($chain) {
      // return object because this entry is being chained
      return $this;
    } else {
      return $this->entry;
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

    // validation callbacks
    $this->beforeValidation();
    $this->afterValidation();

    // before delete callback
    $this->beforeDelete();
    if ($id != - 1) {
      $this->entryId = $id;
    }

    $this->execute($this->buildQueryDelete());

    // after delete callback
    $this->afterDelete();

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

    // set $this->entry so it is accessible in callbacks
    $this->entry = $data;

    // set entryId
    if (isset($this->entry[$this->primaryKey]) && $this->entry[$this->primaryKey] != null) {
      $this->entryId = $this->entry[$this->primaryKey];
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

    // before validation callback
    $this->beforeValidation();

    // validate data
    ////$this->load_validators();
    $this->validateEntry($update);

    // after validation callback
    $this->afterValidation();
    // now that all the callbacks prior to updating/adding the new row have been called
    // we must check for any errors that may have been envoked.
    // if there aren't any then continue as usual.
    // if not then skip adding/updating
    if (! isset($_SESSION[MADEAM_USER_ERROR_NAME]) || count($_SESSION[MADEAM_USER_ERROR_NAME]) < 1) {
      // do standard field formats
      $this->standardFieldFormats();

      // before save callback
      $this->beforeSave();
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
      // set data as row after callbacks have altered $this->entry
      $this->data = $this->entry;

      // filter out fields that don't exist in the model
      $this->data = array_intersect_key($this->data, array_flip($this->setup['standardFields']));

      // if the entryId exists and the record exists then it is an update. Otherwise it's an insert
      if ($update === true) {
        $this->isUpdate = true; // can be used by the dev to figure out if it's an insert or update when using callbacks
        $this->execute($this->buildQueryUpdate());
      } else {
        $this->isInsert = true; // can be used by the dev to figure out if it's an insert or update when using callbacks
        $this->execute($this->buildQueryInsert());
      }

      // grab entry id before it's overwritten by something that happens in afterSave()
      $entryId = $this->insertId();

      // set this so it can be used in afterSave
      $this->entry[$this->primaryKey] = $entryId;

      // grab entry after it's been modified by callbacks
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
      // after save callback
      $this->afterSave();

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
    // do we need to call beforeSave and all the other callbacks or just not worry about it?
    $class = clone $this;

    // set table name
    $class->resourceName = Madeam_Inflector::model_habtm($model, $this->name);
    $class->primaryKey = false;
    $this_key = Madeam_Inflector::model_foreign_key($this->name);
    $relation_key = Madeam_Inflector::model_foreign_key($model);

    //$this->ItemStore->findOne(1,2);
    foreach ($assoc as $val) {
      // check for duplicate
      $class->where("$this_key = '$id' AND $relation_key = '$val'")->unbindAll()->findOne();
      if ($class->numRows() < 1) {
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
    // do we need to call beforeSave and all the other callbacks or just not worry about it?
    $class = clone $this;

    // set table name
    $class->resourceName = Madeam_Inflector::model_habtm($model, $this->name);
    $class->primaryKey = false;
    $this_key = Madeam_Inflector::model_foreign_key($this->name);
    $relation_key = Madeam_Inflector::model_foreign_key($model);

    foreach ($assoc as $val) {
      $class->data = array($this_key => $id, $relation_key => $val);
      $class->where("$this_key = '$id' AND $relation_key = '$val'");
      $class->execute($class->buildQueryDelete());
    }

    unset($class);
  }

  final public function increase($id, $field, $amount = 1) {
    if ($record = $this->fields($field, $this->primaryKey)->findOne($id)) {
      $record[$field] = $record[$field] + $amount;
      if ($this->save($record)) {
        return true;
      }
    }
    return false;
  }

  final public function decrease($id, $field, $amount = 1) {
    if ($record = $this->fields($field, $this->primaryKey)->findOne($id)) {
      $record[$field] = $record[$field] - $amount;
      if ($this->save($record)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Creates table
   * Soon this method will have migration abilities but for now it just creates tables...
   */
  final public function migrate($drop = false) {
    $sql = array(); // our query in array form -- soon to be imploded
    $uniques = array(); // list of fields that have unique values
    // drop table -- this doesn't work yet
    if ($drop == true) {
      $sql[] = 'DROP TABLE IF EXISTS `' . $this->setup['resourceName'] . "`; \n";
    }
    // initial query decleration
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $this->setup['resourceName'] . '` (';
    // add fields
    $fields = array();
    foreach ($this->schema as $name => $opts) {
      $field = array();
      // Field type
      if (! empty($opts['values'])) {
        // values (enum and set)
        $field[] = "`$name` $opts[type]('" . implode("','", $opts['values']) . "')";
      } elseif ($opts['size'] != null) {
        // size
        $field[] = "`$name` $opts[type]($opts[size])";
      } else {
        // neither
        $field[] = "`$name` $opts[type]";
      }
      // NULL?
      if ($opts['null'] == false) {
        $field[] = 'NOT NULL';
      } else {
        $field[] = 'NULL';
      }
      // default value
      if ($opts['default'] != null) {
        $field[] = "DEFAULT '$opts[default]'";
      }
      // auto increment
      if (in_array('auto_increment', $opts)) {
        $field[] = 'AUTO_INCREMENT';
      }
      // primary key
      if (in_array('primaryKey', $opts)) {
        $field[] = 'PRIMARY KEY';
      }
      // unique
      if (in_array('unique', $opts)) {
        $uniques[] = $name;
      }
      // add to list of fields
      $fields[] = implode(' ', $field);
    }
    // add fields
    $sql[] = implode(", ", $fields);
    // add uniques
    if (! empty($uniques)) {
      $sql[] = 'UNIQUE (`' . implode('`,`', $uniques) . '`)';
    }
    // close
    $sql[] = ')';
    // create query string
    $sql = implode(' ', $sql);
    // create table
    $this->execute($sql);
    return true;
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
    $sql = array();
    $table = $this->setup['resourceName'];
    if (empty($this->fields)) {
      if ($this->primaryKey != false) {
        // why should we have to re-state the id? This is a hack! (only for JOINS)
        $sql[] = "SELECT *, $table.$this->primaryKey as $this->primaryKey FROM $table";
      } else {
        $sql[] = "SELECT * FROM $table";
      }
    } else {
      // select only sepcified fields
      $sql[] = "SELECT";
      // determine sql fields versus custom fields
      $fields = $this->_sqlFields;
      $sql[] = implode(",", $fields);
      $sql[] = "FROM $table";
    }
    // joins
    if (! empty($this->_sqlJoins)) {
      foreach ($this->_sqlJoins as $join) {
        $sql[] = "LEFT JOIN";
        $sql[] = $join['table'];
        $sql[] = 'ON ' . $join['on'];
      }
    }
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
    if (! isset($this->data[$this->primaryKey]) || $this->data[$this->primaryKey] == null) {
      unset($this->data[$this->primaryKey]);
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
    //test(implode(' ', $sql));

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
        $sets[] = "$field = '$value'"; // hack! what about SQL functions and Integers?
      }
    } else {
      foreach ($this->fields as $field) {
        $sets[] = "$field = '$this->data[$field]"; // hack! what about SQL functions and Integers?
      }
    }
    // add fields
    $sql[] = implode(", \n", $sets);
    // add where condition
    if ($this->_sqlWhere != 1) {
      $sql[] = 'WHERE ' . $this->_sqlWhere;
    } elseif ($this->entryId != - 1) {
      if (is_int($this->entryId)) {
        $sql[] = 'WHERE ' . $this->primaryKey . ' = ' . $this->entryId;
      } else {
        $sql[] = 'WHERE ' . $this->primaryKey . ' = \'' . $this->entryId . '\'';
      }
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
    //test(implode(' ', $sql));
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
      if (is_int($this->entryId)) {
        $sql[] = 'WHERE ' . $this->primaryKey . ' = ' . $this->entryId;
      } else {
        $sql[] = 'WHERE ' . $this->primaryKey . ' = \'' . $this->entryId . '\'';
      }
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
    //test(implode(' ', $sql));
    return implode(' ', $sql) . ';';
  }

  /**
   * Query Modifiers
   * =======================================================================
   */
  /**
   * Appends WHERE clauses to the WHERE statement
   *
   * @param string $conditions
   */
  final public function where($conditions) {
    if ($this->_sqlWhere != 1) {
      // if a WHERE statement already exists then the new statement is appended to the old with an AND operator
      $this->_sqlWhere .= ' AND (' . $conditions . ')';
    } else {
      // otherwise a new condition is added and replaces the default of "1"
      $this->_sqlWhere = '(' . $conditions . ')';
    }
    // idea -- method chaining is crazy cool
    return $this;
  }

  final public function in($field, $values) {
    if (! empty($values)) {
      $sql = "$field IN ('" . implode("','", $values) . "')";
      $this->where($sql);
    }
    return $this;
  }

  /**
   * Set the order of the sql query
   *
   * @param string $order
   */
  final public function order($order) {
    $this->_sqlOrder = $order;
    // idea -- method chaining is crazy cool
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
    // idea -- method chaining is crazy cool
    return $this;
  }

  /**
   * Sets the fields to be selected, automatically includes primaryKey
   *
   * @param list $fields
   */
  final public function fields() {
    foreach (func_get_args() as $field) {
      if (in_array($field, $this->setup['standardFields'])) {
        $this->_sqlFields[] = $field;
      }
      $this->fields[] = $field;
    }
    // make sure there are no dupes
    $this->fields = array_unique($this->fields);
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

  /**
   * A switch to describe this query
   *
   * @param boolean $explain
   */
  final public function explain($explain = true) {
    $this->_sqlExplain = $explain;
    return $this;
  }

  final public function join($model, $on = false) {
    $model = Madeam_Inflector::modelNameize($model);
    $table = $this->setup['resourceName'];
    if ($on == false) {
      if (in_array($model, array_keys($this->setup['hasAndBelongsToMany']))) {
        $relation = $this->setup['hasAndBelongsToMany'][$model];
        $on = "$table.$this->primaryKey = $relation[joinModel].$relation[foreignKey]";
        $this->_sqlJoins[$model] = array('table' => $relation['joinModel'], 'on' => $on);
      } elseif (in_array($model, array_keys(array_merge($this->setup['hasOne'], $this->setup['hasMany'], $this->setup['belongsTo'])))) {
        $fk = $this->setup['hasModels'][$model]['foreignKey'];
        // I wish there was a better way to do this...
        // make the user type in more info?
        $foreign_table = $this->$model->resourceName;
        $foreign_pk = $this->$model->primaryKey;
        $on = "$table.$fk = $foreign_table.$foreign_pk";
        $this->_sqlJoins[$model] = array('table' => $this->$model->resourceName, 'on' => $on);
      } else {
        test('this feature needs to be fixed - activeRecord join');
        $this->_sqlJoins[$model] = array('table' => $model, 'on' => $on);
      }
    } else {
      $this->_sqlJoins[$model] = array('table' => $this->$model->resourceName, 'on' => $on);
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
    $this->_sqlExplain = false;
    $this->_sqlGroup = false;
    $this->_sqlFields = array();
    $this->_sqlJoins = array();
    $this->fields = array();
    $this->data = array();
    $this->entry = array();
    $this->entryId = - 1;
    $this->depth = 2;
    $this->isInsert = false;
    $this->isUpdate = false;
    //$this->sql = null;
    //$this->link           = false;
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
   * Query Information
   * =======================================================================
   */
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
      return mysql_insert_id();
    }
  }

  /**
   * Return number of rows affected
   *
   * @return int
   */
  final public function affectedRows() {
    return mysql_affectedRows();
  }

  /**
   * Returns number of rows returned
   *
   * @return int
   */
  final public function numRows() {
    if (is_resource($this->link)) {
      return mysql_numRows($this->link);
    } else {
      return 0;
    }
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
