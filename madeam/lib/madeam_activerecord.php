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
 * @version			0.0.6
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

// field types...
define('FIELD_TINYINT', 'TINYINT');
define('FIELD_SMALLINT', 'SMALLINT');
define('FIELD_MEDIUMINT', 'MEDIUMINT');
define('FIELD_INT', 'INT');
define('FIELD_BIGINT', 'BIGINT');
define('FIELD_FLOAT', 'FLOAT');
define('FIELD_DOUBLE', 'DOUBLE');
define('FIELD_DECIMAL', 'DECIMAL');

define('FIELD_CHAR', 'CHAR');
define('FIELD_VARCHAR', 'VARCHAR');

define('FIELD_TINYTEXT', 'TINYTEXT');
define('FIELD_TEXT', 'TEXT');
define('FIELD_MEDIUMTEXT', 'MEDIUMTEXT');
define('FIELD_LONGTEXT', 'LONGTEXT');

define('FIELD_TINYBLOB', 'TINYBLOB');
define('FIELD_BLOB', 'BLOB');
define('FIELD_MEDIUMBLOB', 'MEDIUMBLOB');
define('FIELD_LONGBLOB', 'LONGBLOB');

define('FIELD_ENUM', 'ENUM');
define('FIELD_SET', 'SET');

define('FIELD_DATETIME', 'DATETIME');
define('FIELD_DATE', 'DATE');
define('FIELD_TIMESTAMP', 'TIMESTAMP');
define('FIELD_TIME', 'TIME');
define('FIELD_YEAR', 'YEAR');

class madeam_activerecord extends madeam_model {
  protected $resource_name        = null;     // name of the database table that holds the records for this model
  protected $label                = null;     // name of field that acts as label for a row

  protected $conn                 = false;    // connection resource

  protected $data                 = array();
  protected $entry                = array();  // represents a single row
  protected $sql                  = null;  
  protected $link                 = null;     // query resource link
  protected $entry_id             = -1;       // row id
	
	protected $is_insert						= false;
	protected $is_update						= false;
	
  private $sql_explain            = false;
  private $sql_start              = false;
  private $sql_fields							= array();
  private $sql_range              = false;
  private $sql_order              = false;
  private $sql_where              = '1';
  private $sql_joins              = array();
  private $sql_group              = false;
  
  /**
   * Magic method allows for special query functions like find_all_by_name('Joshua');
   *
   * @param string $name
   * @param array $args
   * @return array/boolean
   */
  public function __call($name, $args) {
    $match = array();
    if (preg_match("/^find_([a-z]+)_by_(.*)/", $name, $match)) {
      $this->where($match[2] . " = '$args[0]'");
      $function = 'find_' . $match[1];
      return $this->$function();
    }
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
    global $db_connection; // this is the only good use of globals. But still I wish we could get rid of the global part
    
    // if we connect here we don't need to connect to the database until we execute a query
    // therefore if all we're doing is loading a cached page we won't need a database connection
    if (!is_resource($db_connection)) {      
      if (@DB_PORT) {
        $db_connection = @mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASS);
      } else {
        $db_connection = @mysql_connect(DB_HOST, DB_USER, DB_PASS);
      }
      
      if (is_resource($db_connection)) {
        if (!@mysql_select_db(DB_NAME, $db_connection)) {
          // failed to find selected database
          madeam_logger::log(mysql_error(), 0);
        }
      } else {
        // failed to connect to the database
        madeam_logger::log(mysql_error(), 0);
      }        
    }
    
    // execute mysql query
    $this->link = mysql_query($sql);
    
    // log query
    madeam_logger::log($sql, 100);
    
    // log sql error if any
    if (mysql_error()) {      
      madeam_logger::log(mysql_error());
    }

    return $this->link;
  }

	final public function query($sql) {
	  // execute query
		$this->link =	$this->execute($sql);

		// check to see if this query returns a resource -- why not just check to see if it's a resource instead?
		preg_match('/^DESCRIBE|SELECT/', $sql, $matchs);
    
		if (count($matchs) > 0) {
		  if ($this->num_rows() > 0) {
  			while ($this->entry = mysql_fetch_assoc($this->link)) {
			    $this->prepare_result(); // should this be done?
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
	  return $this->query("DESCRIBE $this->resource_name");
	}
	
  /**
   * Returns many rows. The query generated by find_all can be tweaked with the methods below
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
    $this->before_find();
    
    // derive and cache custom fields instead of doing it for each entry -- this code used to be inside of prepare_result
    if (empty($this->setup['custom_fields'])) {
      $this->derive_custom_fields();
    }
    
    // if this is a child model then filter the results to make sure they are related to this model's parent
    // this stuff is for when chaining models like: 
    // $this->article->find_one(32, true)->comment->find_all();
    // where article is the parent of comment
    if ($this->parent && $this->sql_where == '1') {
      $this->where($this->parent->setup['has_models'][$this->name]['foreign_key'] . " = '" . $this->parent->entry[$this->parent->primary_key] . "'");
    }

    // build select query and set resource link
    $this->link = $this->execute($this->build_select_query());
     

    if (is_resource($this->link)) {
      // get data
      while ($this->entry = mysql_fetch_assoc($this->link)) {
        // adds custom fields
        $this->prepare_result();
        
        // find related content
        if ($this->depth > 0) {
          // find has_ones
          foreach ($this->setup['has_one'] as $model => $params) {
            $fkey = $params['foreign_key'];
            if (!in_array($model, array_values($this->unbound)) && !in_array($model, array_keys($this->sql_joins))) {
              // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
              // clone object so we don't interupt it's state with reset()
              
              $tempmodel = clone $this->{$params['model']};
              $tempmodel->name = $model;
              
              $this->entry[$params['foreign_key']] = $tempmodel->find_one($this->entry[$fkey]);
              unset($tempmodel);
            }
          }
          
          // find belongs_tos
          foreach ($this->setup['belongs_to'] as $model => $params) {
            $fkey = $params['foreign_key'];
            if (!in_array($model, array_values($this->unbound)) && !in_array($model, array_keys($this->sql_joins))) {
              // we need to solve for the foreign key name some where here instead of assuming it'll always be named after the table
              // clone object so we don't interupt it's state with reset()
              
              $tempmodel = clone $this->{$params['model']};
              // change the model name because we can't always assume that the name will be the same.
              // An example of this is when you create a self-refrencing relationship in a table and name the relationship "sub_model" or "parent_model"
              $tempmodel->name = $model;
              
              @$this->entry[$params['foreign_key']] = $tempmodel->find_one($this->entry[$fkey]);
              unset($tempmodel);
            }
          }
          
          // find has_manies
          foreach ($this->setup['has_many'] as $model => $params) {
            // do not call if the user has not specified to call this data
            if (!in_array($model, array_values($this->unbound)) && !in_array($model, array_keys($this->sql_joins))) {
              // clone object so we don't interupt it's state with reset()
              $tempmodel = clone $this->{$params['model']};
              $tempmodel->name = $model;
              
              $this->entry[madeam_inflector::model_tableize($model)] = $tempmodel->find_all();
              unset($tempmodel);
            }
          }
          
          // find has and belongs to manies
          foreach ($this->setup['has_and_belongs_to_many'] as $model => $params) {            
            if ((in_array($model,$this->fields) || empty($this->fields)) && !in_array($model, array_values($this->unbound)) && !in_array($model, array_keys($this->sql_joins))) {
              $tempmodel = clone $this->{$params['model']};
              $tempmodel->name = $model;
              
              $this->entry[madeam_inflector::model_tableize($params['model'])] = $tempmodel
                ->join($this->name)
                ->find_all();
                
              unset($tempmodel);
            }
          }
          
        }
        
        $this->data[] = $this->entry;
      }
    }

    
    // find callback
    $this->after_find();

    // return data
    if ($this->num_rows() > 0) { 
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
  final public function find_all() {
    return $this->find();
  }
  
  /**
   * This is find_all except that it returns a list
   * @see find
   * @param string $value
   * @param string $label
   * @return array/boolean
   */
  final public function find_list($value, $label = false) {           
    if ($results = $this->find()) {      
      return $this->generate_list($results, $value, $label);
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
  final public function generate_list($result, $value, $label = false) {
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
   * Find a single row based on value of primary_key
   *
   * @param string/int $id
   * @return array/false
   */
  final public function find_one($id = -1, $chain = false) {
    $this->entry = array();
    
    // set entry_id
    if ($id != -1) { $this->entry_id = $id; }

    // set where
    if ($this->entry_id != -1 && $this->primary_key != false) {
      $this->where("$this->resource_name.$this->primary_key = '$id'"); 
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
    $this->before_validation();
    $this->after_validation();

    // before delete callback
    $this->before_delete();

    if ($id != -1) { $this->entry_id = $id; }
    $this->execute($this->build_delete_query());

    // after delete callback
    $this->after_delete();

    // check success
    if ($this->affected_rows() > 0) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Creates/Updates a record in the database
   *
   * An update is assumed if the primary_key has a value
   *
   * @param array $data
   */
  final public function save($data) {
    // true/false
    $update = false;
    
    // set $this->entry so it is accessible in callbacks
    $this->entry = $data;
    
    // set entry_id
    if (isset($this->entry[$this->primary_key]) && $this->entry[$this->primary_key] != null) {
      $this->entry_id = $this->entry[$this->primary_key];
    }
    
    // check to see whether this is going to be an insert or an update
    $name = get_class($this);
    $inst = new $name;

    // if the entry_id exists and the record exists then it is an update. Otherwise it's an insert
    if ($this->entry_id != -1 && $inst->find_one($this->entry_id)) {
      $update = true;
    }
    
    // unset duplicate of this model to save on sweet sweet memory
    unset($inst);

    // before validation callback
    $this->before_validation();

    // validate data
    $this->load_validators();
    $this->validate_entry($update);

    // after validation callback
    $this->after_validation();
    
    // now that all the callbacks prior to updating/adding the new row have been called
    // we must check for any errors that may have been envoked.
    // if there aren't any then continue as usual.
    // if not then skip adding/updating
    if (!isset($_SESSION[USER_ERROR_NAME]) || count($_SESSION[USER_ERROR_NAME]) < 1) {
      // do standard field formats
      $this->standard_field_formats();
      
      // before save callback
      $this->before_save();
      
      /*
      // relations
      $relations = array();
      
      // determine if any of the fields represents any of this model's relationships
      foreach ($this->entry as $field => $value) {
        if (is_array($value)) {
          $relations[$field] = $value;
          
          // instead of just unsetting it, it should get it's primary_key value!
          $this->entry[$field] = $value[$this->{madeam_inflector::model_nameize($field)}->primary_key];
        }
      }
      */
      
      // set data as row after callbacks have altered $this->entry
      $this->data = $this->entry;

      // if the entry_id exists and the record exists then it is an update. Otherwise it's an insert
      if ($update === true) {
				$this->is_update = true; // can be used by the dev to figure out if it's an insert or update when using callbacks
        $this->execute($this->build_update_query());
      } else {
				$this->is_insert = true; // can be used by the dev to figure out if it's an insert or update when using callbacks
        $this->execute($this->build_insert_query());
      }
      
      // grab entry id before it's overwritten by something that happens in after_save()
      $entry_id = $this->insert_id();
			
			// set this so it can be used in after_save
			$this->entry[$this->primary_key] = $entry_id;
      
      /*
      if ($entry_id) {
        // check for fields that are arrays
        foreach ($relations as $model => $value) {
          $model = madeam_inflector::model_nameize($model);
          if (in_array($model, array_keys($this->setup['has_and_belongs_to_many']))) {
            $this->habtm_add($model, $entry_id, $value);
          } else {
            $this->$model->save($value);
          }
        }
      }
      */

      // after save callback
      $this->after_save();

      // reset all sql values and data
      $this->reset();

      return $entry_id;
    } else {
      // reset all sql values and data
      $this->reset();

      return false;
    }

    // reset all sql values and data
    $this->reset();

    // return data
    if ($this->affected_rows() > 0) { return $this->data; }

    // failed to return any rows
    return false;
  }

  final public function habtm_add($model, $id, $assoc = array()) {
    if (!is_array($assoc)) { $assoc = array($assoc); }
    
    // clone current class just because...
    // this is not cool though because this means shit is going to be validated when it shouldn't be.
    // do we need to call before_save and all the other callbacks or just not worry about it?
    $class = clone $this;
    
    // set table name
    $class->resource_name = madeam_inflector::model_habtm($model, $this->name);
    $class->primary_key = false;
    
    $this_key = madeam_inflector::model_foreign_key($this->name);
    $relation_key = madeam_inflector::model_foreign_key($model);
    
    //$this->ItemStore->find_one(1,2);
    
    foreach ($assoc as $val) {
      // check for duplicate
      $class->where("$this_key = '$id' AND $relation_key = '$val'")->unbind_all()->find_one();
      if ($class->num_rows() < 1) {
        $class->data = array($this_key => $id, $relation_key => $val);    
        $class->execute($class->build_insert_query());
      }
    }
    
    unset($class);
  }
  
  
  final public function habtm_delete($model, $id, $assoc = array()) {
    if (!is_array($assoc)) { $assoc = array($assoc); }
    
    // clone current class just because...
    // this is not cool though because this means shit is going to be validated when it shouldn't be.
    // do we need to call before_save and all the other callbacks or just not worry about it?
    $class = clone $this;
    
    // set table name
    $class->resource_name = madeam_inflector::model_habtm($model, $this->name);
    $class->primary_key = false;
    
    $this_key = madeam_inflector::model_foreign_key($this->name);
    $relation_key = madeam_inflector::model_foreign_key($model);
    
    foreach ($assoc as $val) {
      $class->data = array($this_key => $id, $relation_key => $val);
      $class->where("$this_key = '$id' AND $relation_key = '$val'");
      $class->execute($class->build_delete_query());
    }
    
    unset($class);
  }
	
	
 	final public function increase($id, $field, $amount = 1) {
		if ($record =$this->fields($field, $this->primary_key)->find_one($id)) {
			$record[$field] = $record[$field] + $amount;
			if ($this->save($record)) {
				return true;
			}
		}
		
		return false;
	}
	
	final public function decrease($id, $field, $amount = 1) {
		if ($record =$this->fields($field, $this->primary_key)->find_one($id)) {
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
    $sql      = array(); // our query in array form -- soon to be imploded
    $uniques  = array(); // list of fields that have unique values
    
    // drop table -- this doesn't work yet
    if ($drop == true) {
      $sql[] = 'DROP TABLE IF EXISTS `' . $this->resource_name . "`; \n";
    }
    
    // initial query decleration
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $this->resource_name . '` (';

    // add fields
    $fields = array();
    foreach ($this->schema as $name => $opts) {
      $field = array();
      
      // Field type
      if (!empty($opts['values'])) {
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
      if (in_array('primary_key', $opts)) {
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
    if (!empty($uniques)) {
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
  final private function build_select_query() {
    $sql = array();
    
    if (empty($this->fields)) {
      if ($this->primary_key != false) {
        // why should we have to re-state the id? This is a hack! (only for JOINS)
        $sql[] = "SELECT *, $this->resource_name.$this->primary_key as $this->primary_key FROM $this->resource_name";
      } else {
        $sql[] = "SELECT * FROM $this->resource_name";
      }
    } else {
      // select only sepcified fields
      $sql[] = "SELECT" ;

      // determine sql fields versus custom fields
      $fields =$this->sql_fields;

      $sql[] = implode(",", $fields);
      $sql[] = "FROM $this->resource_name";
    }
    
    // joins
    if (!empty($this->sql_joins)) {      
      foreach ($this->sql_joins as $join) {
        $sql[] = "LEFT JOIN";
        $sql[] = $join['table'];
        $sql[] = 'ON ' . $join['on'];
      }
    }

    // add where
    $sql[] = 'WHERE ' . $this->sql_where;

    // add order
    if ($this->sql_order) {
      $sql[] = 'ORDER BY ' . $this->sql_order;
    }

    // add limit
    if ($this->sql_start !== false && $this->sql_range !== false) {
      $sql[] = "LIMIT $this->sql_start, $this->sql_range";
    } elseif ($this->sql_start !== false && $this->sql_range === false) {
      $sql[] = "LIMIT $this->sql_start";
    }

    return implode(' ', $sql) . ';';
  }

  /**
   * Create Insert Query
   *
   * @return string
   */
  final private function build_insert_query() {
    $sql = array();

    // remove primary key insert if it is null
    if (!isset($this->data[$this->primary_key]) || $this->data[$this->primary_key] == null) {
      unset($this->data[$this->primary_key]);
    }

    // add table name
    $sql[] = 'INSERT INTO ' . $this->resource_name;

     // add fields and values
    if (empty($this->fields)) {
      // add fields
      $sql[] = '(' . implode(',', array_keys($this->data)) . ')';

      // close fields, open values
      $sql[] = 'VALUES';

      // add values
      $sql[] = "('" . @implode("','", array_values($this->data)) . "')";
    } else {
      // add fields
      $sql[] = implode("','",$this->fields);

      // close fields, open values
      $sql[] = 'VALUES';

      // add values
      foreach ($this->fields as $field) { $values[] = $this->data[$field]; }
      $sql[] = "('" . implode(',', $values) . "')";
    }

    // build query
    //test(implode(' ', $sql));
    return implode(' ', $sql) . ';';
  }

  /**
   * Create Update Query
   *
   * @return string
   */
  final private function build_update_query() {
    $sql = array();

    // add table name
    $sql[] = 'UPDATE ' . $this->resource_name . ' SET';

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
    if ($this->sql_where != 1) {
      $sql[] = 'WHERE ' . $this->sql_where;
    } elseif ($this->entry_id != -1) {
      if (is_int($this->entry_id)) {
        $sql[] = 'WHERE ' . $this->primary_key . ' = ' . $this->entry_id;
      } else {
        $sql[] = 'WHERE ' . $this->primary_key . ' = \'' . $this->entry_id . '\'';
      }
    } else {
      return false;
    }

    // add limit
    if ($this->sql_start && $this->sql_range) {
      $sql[] = "LIMIT $this->start, $this->sql_range";
    } elseif ($this->sql_start && !$this->sql_range) {
      $sql[] = "LIMIT $this->sql_start";
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
  final private function build_delete_query() {
    $sql = array();

    // add table name
    $sql[] = 'DELETE FROM ' . $this->resource_name;

    // add where condition
    if ($this->sql_where != 1) {
      $sql[] = 'WHERE ' . $this->sql_where;
    } elseif ($this->entry_id != -1) {
      if (is_int($this->entry_id)) {
        $sql[] = 'WHERE ' . $this->primary_key . ' = ' . $this->entry_id;
      } else {
        $sql[] = 'WHERE ' . $this->primary_key . ' = \'' . $this->entry_id . '\'';
      }
    } else {
      return false;
    }

    // add limit
    if ($this->sql_start && $this->sql_range) {
      $sql[] = "LIMIT $this->start, $this->sql_range";
    } elseif ($this->sql_start && !$this->sql_range) {
      $sql[] = "LIMIT $this->sql_start";
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
    if ($this->sql_where != 1) {
      // if a WHERE statement already exists then the new statement is appended to the old with an AND operator
      $this->sql_where .= ' AND (' . $conditions . ')';
    } else {
      // otherwise a new condition is added and replaces the default of "1"
      $this->sql_where = '(' . $conditions . ')';
    }
    
    // idea -- method chaining is crazy cool
    return $this;
  }
  
  final public function in($field, $values) {
    
    if (!empty($values)) {
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
    $this->sql_order = $order;
    
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
    $this->sql_start = $start;
    $this->sql_range = $range;
    
    // idea -- method chaining is crazy cool
    return $this;
  }

  /**
   * Sets the fields to be selected, automatically includes primary_key
   *
   * @param list $fields
   */
  final public function fields() {
    // get custom fields...?
    if (empty($this->setup['custom_fields'])) {
      $this->derive_custom_fields();
    }
    
    foreach(func_get_args() as $field) {
      if (in_array($field, $this->setup['standard_fields'])) {
	    	$this->sql_fields[] = $field;
  		}
    
  		$this->fields[] = $field;
    }
    
    $this->fields = array_merge($this->setup['custom_fields'], $this->fields);
    
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
    $this->sql_explain = $explain;
    
    return $this;
  }
  
  final public function join($model, $on = false) {
    
    $model = madeam_inflector::model_nameize($model);
    
    if ($on == false) {
      if (in_array($model, array_keys($this->setup['has_and_belongs_to_many']))) {
        
        $relation = $this->setup['has_and_belongs_to_many'][$model];
        
        $on = "$this->resource_name.$this->primary_key = $relation[join_model].$relation[foreign_key]";
           
        $this->sql_joins[$model] = array('table' => $relation['join_model'], 'on' => $on);
        
      } elseif (in_array($model, array_keys(array_merge($this->setup['has_one'], $this->setup['has_many'], $this->setup['belongs_to'])))) {
        
        $fk = $this->setup['has_models'][$model]['foreign_key'];
        
        // I wish there was a better way to do this...
        // make the user type in more info?
        $foreign_table  = $this->$model->resource_name;
        $foreign_pk     = $this->$model->primary_key;
        
        $on = "$this->resource_name.$fk = $foreign_table.$foreign_pk";
        
        $this->sql_joins[$model] = array('table' => $this->$model->resource_name, 'on' => $on);
      } else {
        t('this feature needs to be fixed - activeRecord join');
        $this->sql_joins[$model] = array('table' => $model, 'on' => $on);
      } 
    } else {
      $this->sql_joins[$model] = array('table' => $this->$model->resource_name, 'on' => $on);
    }
    
    return $this;
  }

  /**
   * Resets all the sql values and data
   */
  final public function reset() {
    $this->sql_where      = '1';
    $this->sql_order      = false;
    $this->sql_start      = false;
    $this->sql_range      = false;
    $this->sql_explain    = false;
    $this->sql_group      = false;
    $this->sql_fields     = array();
    $this->sql_joins      = array();
    $this->fields         = array();
    $this->data           = array();
    $this->entry          = array();
    $this->entry_id       = -1;
		$this->depth					= 2;
		
		$this->is_insert			= false;
		$this->is_update			= false;
    //$this->link           = false;
    //$this->setup['custom_fields'] = array();
        
    // reset child models 
    // this is so we can do stuff like...
    // $this->article->comment->depth(2)->fields('id', 'title');
    // $article = $this->article->find_one(32);
    // which will return an article with all of it's comments defined by it's sql modifiers like depth() and fields() and others
    foreach ($this->setup['has_models'] as $model => $info) {
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
  final public function insert_id() {
    // this is necessary when the programmer has to specify a primary_key value when the
    // primary_key does not auto increment or is not an integer
    if ($this->entry_id != -1) {
      return $this->entry_id;
    } else {
      return mysql_insert_id();
    }
  }

  /**
   * Return number of rows affected
   *
   * @return int
   */
  final public function affected_rows() {
    return mysql_affected_rows();
  }

  /**
   * Returns number of rows returned
   *
   * @return int
   */
  final public function num_rows() {
    if (is_resource($this->link)) {
      return mysql_num_rows($this->link);
    } else {
      return 0;
    }
  }

  final public function get_table_fields() {
    $result = $this->query("SHOW COLUMNS FROM " . $this->resource_name);
    
    $fields = array();
    while ($row = mysql_fetch_assoc($result)) {
      $fields[] = $row;
    }
    
    return $fields;
  }
  
  final public function create_form() {
    
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
  final protected function format_datetime($date = array()) {
    if (is_array($date)) {
      //2006-12-26 19:26:19
      $date['year']   ? true : $date['year']    = '0000';
      $date['month']  ? true : $date['month']   = '00';
      $date['day']    ? true : $date['day']     = '00';
      $date['hour']   ? true : $date['hour']    = '00';
      $date['minute'] ? true : $date['minute']  = '00';
      $date['second'] ? true : $date['second']  = '00';
      return "$date[year]-$date[month]-$date[day] $date[hour]:$date[minute]:$date[second]";
    } else {
      return $date;
    }
  }

  final private function format_datetime_field($field, $default) {
    if (isset($this->entry[$field])) {
      if (is_string($this->entry[$field]) && $this->entry[$field] == null) {
        $this->entry[$field] = date($default);
      } else {
        $this->entry[$field] = $this->format_datetime($this->entry[$field]);
      }
    }
  }

  final protected function standard_field_formats() {
    // created_on
    $this->format_datetime_field('created_on', 'Y-m-d 00:00:00');

    // created_at
    $this->format_datetime_field('created_at', 'Y-m-d H:i:s');

    // updated_on
    $this->format_datetime_field('updated_on', 'Y-m-d 00:00:00');

    // updated_at
    $this->format_datetime_field('updated_at', 'Y-m-d H:i:s');
  }
}
?>