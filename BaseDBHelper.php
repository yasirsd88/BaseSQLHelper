<?php

/**
 * Description of Singelton
 *
 * @author yasirmehmood
 */
//$base_path = realpath(__DIR__) . DIRECTORY_SEPARATOR;
//require_once $base_path . 'Validator.php';

class singleton {

// ensure that only a single instance exists for each class. {

    public static function &getInstance($class, $arg1 = null) {
// implements the 'singleton' design pattern.
        static $instances = array();  // array of instance names

        if (array_key_exists($class, $instances)) {
// instance exists in array, so use it
            $instance = & $instances[$class];
        } else {
// load the class file (if not already loaded)
            if (!class_exists($class)) {
                require_once(AppPath . "mode" . DS . $class);
            } // if
// instance does not exist, so create it
            $instances[$class] = new $class($arg1);
            $instance = & $instances[$class];
        } // if

        return $instance;
    }

}

// getInstance
// singleton

/**
 * Description of BaseDB
 *
 * @author yasirmehmood
 */
class BaseDB {

    protected $db;
    private $relations;

    public static function getIntance() {
        return singleton::getInstance('BaseDB');
    }

    function __construct() {
        $this->db = $GLOBALS['connection'];
        if (!$this->db)
            die($this->debug(true));
        mysql_set_charset('utf8', $this->db);
    }

    // end constructor
    function select($query, $maxRows = 0, $pageNum = 0) {
        $this->query = $query;
        // start limit if $maxRows is greater than 0
        if ($maxRows > 0) {
            $startRow = $pageNum * $maxRows;
            $query = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
        }
//		echo $query;
        $result = mysql_query($query, $this->db);
        if ($this->error())
            die($this->debug());
        $output = array();
        for ($n = 0; $n < mysql_num_rows($result); $n++) {
            $row = mysql_fetch_assoc($result);
            $output[$n] = $row;
        }
        return $output;
    }

    /**
     *
     * @param type $resource
     * @return type 
     */
    // remove duplicate elements from n-dimensional arrays
    function uniqueArray($nDimArray) {
        foreach ($nDimArray as $key => $val) {
            $nDimArray[$key] = array_map("unserialize", array_unique(array_map("serialize", $val)));
            foreach ($nDimArray[$key] as $key1 => $val1) {
                $nDimArray[$key][$key1] = array_filter($val1);
            }
            $nDimArray[$key] = array_filter($nDimArray[$key]);
        }
        return $nDimArray;
    }

    function mysql_fetch_qualified_array($resource) {
        $count = 0;
        $qualifiedarray = array();
        while ($basearray = mysql_fetch_array($resource)) {
            $temp = array();
            for ($i = 0; $i < mysql_num_fields($resource); $i++) {
                $table = mysql_field_table($resource, $i);
                $field = mysql_field_name($resource, $i);
                if ($this->relations) {
                    if ($count == 0 && key_exists('hasOne', $this->relations) && in_array($table, $this->relations['hasOne'])) {
                        $qualifiedarray[$table][$field] = $basearray[$i];
                    } else {
                        if (!(key_exists('hasOne', $this->relations) && in_array($table, $this->relations['hasOne'])))
                            $qualifiedarray[$table][$count][$field] = $basearray[$i];
                    }
                }else {
                    $qualifiedarray[$count][$table][$field] = $basearray[$i];
                }
            }
            $count++;
        }
        return $this->uniqueArray($qualifiedarray);
    }

    /**
     *
     * @param type $query
     * @param type $maxRows
     * @param type $pageNum
     * @return type 
     */
    function select_model($query, $relations, $maxRows = 0, $pageNum = 0) {
        $this->query = $query;
        $this->relations = $relations;
        // start limit if $maxRows is greater than 0
        if ($maxRows > 0) {
            $startRow = $pageNum * $maxRows;
            $query = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
        }
        $result = mysql_query($query, $this->db);
        if ($this->error())
            die($this->debug());
        return $this->mysql_fetch_qualified_array($result);
    }

// end select
    function misc($query) {
        $this->query = $query;
        $result = mysql_query($query, $this->db);
        if ($this->error())
            die($this->debug());
        if ($result == TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function numrows($query) {
        $this->query = $query;
        $result = mysql_query($query, $this->db);
        return mysql_num_rows($result);
    }

    function insert($tablename, $record) {
        if (!is_array($record))
            die($this->debug("array", "Insert", $tablename));
        $count = 0;
        foreach ($record as $key => $val) {
            if ($count == 0) {
                $fields = "`" . $key . "`";
                $values = $val;
            } else {
                $fields .= ", " . "`" . $key . "`";
                $values .= ", " . $val;
            }
            $count++;
        }
        $query = "INSERT INTO " . $tablename . " (" . $fields . ") VALUES (" . $values . ")";
        $this->query = $query;
        //echo $query;
        mysql_query($query, $this->db);
        if ($this->error())
            die($this->debug());
        //if ($this->affected() > 0) return true; else return false;
        return $this->insertid();
    }

// end insert
    function update($tablename, $record, $where) {
        if (!is_array($record))
            die($this->debug("array", "Update", $tablename));
        $count = 0;
        foreach ($record as $key => $val) {
            if ($count == 0)
                $set = "`" . $key . "`" . "=" . $val;
            else
                $set .= ", " . "`" . $key . "`" . "= " . $val;
            $count++;
        }
        $query = "UPDATE " . $tablename . " SET " . $set . " WHERE " . $where;
        // echo $query; Just Testing
        $this->query = $query;
        mysql_query($query, $this->db);
        if ($this->error())
            die($this->debug());
        if ($this->affected() > 0)
            return true; else
            return false;
    }

// end update
    function delete($tablename, $where, $limit = "") {
        $query = "DELETE from " . $tablename . " WHERE " . $where;
        if ($limit != "")
            $query .= " LIMIT " . $limit;
        $this->query = $query;
        mysql_query($query, $this->db);
        if ($this->error())
            die($this->debug());
        if ($this->affected() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

// end delete
    function mySQLSafe($value, $quote = "'") {
        // strip quotes if already in
        $value = htmlspecialchars($value, ENT_NOQUOTES);
        $value = str_replace(array("\'", "'"), "&#39;", $value);
        // Stripslashes
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        // Quote value
        if (version_compare(phpversion(), "4.3.0") == "-1") {
            $value = mysql_escape_string($value);
        } else {
            $value = mysql_real_escape_string($value);
        }
        $value = $quote . $value . $quote;
        return $value;
    }

    public static function SQLSafe($value, $quote = "'") {
        // strip quotes if already in
        $value = htmlspecialchars($value, ENT_NOQUOTES);
        $value = str_replace(array("\'", "'"), "&#39;", $value);
        // Stripslashes
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        // Quote value
        if (version_compare(phpversion(), "4.3.0") == "-1") {
            $value = mysql_escape_string($value);
        } else {
            $value = mysql_real_escape_string($value);
        }
        $value = $quote . $value . $quote;
        return $value;
    }

    function debug($type = "", $action = "", $tablename = "") {
        switch ($type) {
            case "connect":
                $message = "MySQL Error Occured";
                $result = mysql_errno() . ": " . mysql_error();
                $query = "";
                $output = "Could not connect to the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
                break;
            case "array":
                $message = $action . " Error Occured";
                $result = "Could not update " . $tablename . " as variable supplied must be an array.";
                $query = "";
                $output = "Sorry an error has occured accessing the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
                break;
            default:
                if (mysql_errno($this->db)) {
                    $message = "MySQL Error Occured";
                    $result = mysql_errno($this->db) . ": " . mysql_error($this->db);
                    $output = "Sorry an error has occured accessing the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
                } else {
                    $message = "MySQL Query Executed Succesfully.";
                    $result = mysql_affected_rows($this->db) . " Rows Affected";
                    $output = "view logs for details";
                }
                $linebreaks = array("\n", "\r");
                if ($this->query != "")
                    $query = "QUERY = " . str_replace($linebreaks, " ", $this->query); else
                    $query = "";
                break;
        }
        $output = "<b style='font-family: Arial, Helvetica, sans-serif; color: #0B70CE;'>" . $message . "</b><br />\n<span style='font-family: Arial, Helvetica, sans-serif; color: #000000;'>" . $result . "</span><br />\n<p style='Courier New, Courier, mono; border: 1px dashed #666666; padding: 10px; color: #000000;'>" . $query . "</p>\n";
        if (!defined('ENVIRONMENT'))
            define('ENVIRONMENT', 'DEVELOPMENT');
        if (ENVIRONMENT == 'DEVELOPMENT') {
            echo $output;
        }
        return ""; //$output;
    }

    function error() {
        if (mysql_errno($this->db))
            return true; else
            return false;
    }

    function insertid() {
        return mysql_insert_id($this->db);
    }

    function affected() {
        return mysql_affected_rows($this->db);
    }

    function close() { // close conection
        mysql_close($this->db);
    }

    static function getSlug($title) {
        $finalSlug = mb_strtolower($title);
        $finalSlug = preg_replace('/[^a-zA-Z0-9-_ \[\]\.\(\)]/s', '', $finalSlug);
        $finalSlug = str_replace(" ", "-", $finalSlug);
        $finalSlug = str_replace("--", "-", $finalSlug);
        return $finalSlug;
    }

}

/**
 * Description of BaseDBHelper
 *
 * @author yasirmehmood
 */
class BaseDBHelper {

    protected $data = array();
    private $_table = '';
    private $_primary_key = 'id';
    private $_class = '';
    private $_prefix = '';
    private $fields = '*';
    private $_db;
    private $_query;
    private $query;
    protected $collection;
    // singleton instance 
    private static $instance;

    // getInstance method 
    public static function getInstance() {
        return singleton::getInstance(get_called_class());
    }

    public function __construct() {
        self::$instance = $this;
        $this->_class = get_class($this);
        if (isset($this->primary_key) && !empty($this->primary_key)) {
            $this->_primary_key = $this->primary_key;
        }
        if (isset($this->tablename) && !empty($this->tablename)) {
            $this->_table = $this->tablename;
        } else if (empty($this->table)) {
            $this->_table = $this->_prefix . strtolower(get_class($this));
        }
        $this->_db = BaseDB::getIntance();
        $this->query = "";
        $this->_query = "";
    }

    public function get_prefix() {
        return $this->_prefix;
    }

    public function set_prefix($_prefix) {
        $this->_prefix = $_prefix;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function select($fields = '*') {
        $this->_query = "SELECT $fields ";
        return $this;
    }

    public function from($table = '') {
        $this->_query .= "FROM $table ";
        return $this;
    }

    public function where($condition = '', $val = array()) {
        $this->_query .= "WHERE " . $this->populateValues($condition, $val) . " ";
        return $this;
    }

    public function get_query() {
        return $this->_query;
    }

    public function getQuery() {
        return $this->query;
    }

    public function left($join = '', $values = array()) {
        $this->_query .= "LEFT JOIN $join ";
        $this->_query = $this->populateValues($this->_query, $values);
        return $this;
    }

    public function right($join = '', $values = array()) {
        $this->_query .= "RIGHT JOIN $join ";
        $this->_query = $this->populateValues($this->_query, $values);
        return $this;
    }

    public function join($join = '', $values = array()) {
        $this->_query .= "INNER JOIN $join ";
        $this->_query = $this->populateValues($this->_query, $values);
        return $this;
    }

    public function group($group = '') {
        $this->_query .= "GROUP BY $group ";
        $this->_query = $this->populateValues($this->_query, $values);
        return $this;
    }

    public function order($order = '') {
        $this->_query .= "ORDER BY $order ";
        return $this;
    }

    public function limit($min, $max = '') {
        $this->_query .= "LIMIT $min " . (!empty($max) ? ", $max " : '');
        return $this;
    }

    public function execute() {
        $this->query($this->_query);
        return $this;
    }

    public function executeModel() {
        $this->query = $this->_query;
        $this->queryModelRecord();
        return $this;
    }

    private function SQLINJECTION($values) {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $values[$key] = BaseDB::SQLSafe($value);
            }
        }
        return $values;
    }

    protected function cleanValues($values) {
        foreach ($values as $key => $value) {
            $values[$key] = BaseDB::SQLSafe($value);
        }
        return $values;
    }

    public function fields($fields) {
        $this->fields = $fields;
        return $this;
    }

    public function populateValues($string, $values) {
        $values = $this->SQLINJECTION($values);
        return (count($values) > 0) ? preg_replace(array_fill(0, count($values), "/\?/"), $values, $string, 1) : $string;
    }

    public function query($query, $values = array()) {
        $values = $this->SQLINJECTION($values);
        $this->query = $this->populateValues($query, $values);
        return $this->queryRecord();
    }

    public function queryRecord() {
        $record = $this->_db->select($this->query);
        $this->collection = $this->createAssociateCollection($record);
        return $this->collection;
    }

    public function queryModelRecord() {
        $relations = isset($this->relations) ? $this->relations : array();
        $this->collection = $this->_db->select_model($this->query, $relations);
        return $this->collection;
    }

    public function set($data = array()) {
        $this->data = $data;
        return $this;
    }

    public function hasRecord() {
        return (count($this->collection) > 0);
    }

    public function find($data = 'all') {
        if ($data == 'all')
            $this->_query = "SELECT $this->fields FROM " . $this->_table . " ";
        else
            $this->_query = "SELECT $this->fields FROM " . $this->_table . " LIMIT 1 ";
        return $this;
    }

    public function filter($condition, $values = array()) {
        $this->query = "SELECT $this->fields FROM " . $this->_table . " WHERE $condition";
        return $this->query($this->query, $values);
    }

    public function delete($condition = "", $values = array()) {
        if (isset($this->data[$this->_primary_key])) {
            return $this->_db->delete($this->_table, "$this->_primary_key = " . $this->_db->mySQLSafe($this->data[$this->_primary_key]));
        } else if ($condition != "") {
            $condition = $this->populateValues($condition, $values);
            return $this->_db->delete($this->_table, $condition);
        }
    }

    public function update($data, $condition = "", $values = array()) {
        $data = $this->SQLINJECTION($data);
        if ($condition != "") {
            $condition = $this->populateValues($condition, $values);
        }
        return $this->_db->update($this->_table, $data, $condition);
    }

    public function saveifnotexist() {
        $fields = "";
        $values = "";
        $count = 0;
        $condition = "";
        $data_values = $this->data;
        $this->data = $this->SQLINJECTION($this->data);
        foreach ($this->data as $key => $val) {
            if ($count == 0) {
                $fields = "`" . $key . "`";
                $values = $val;
                $condition = " $key = ? ";
            } else {
                $fields .= ", " . "`" . $key . "`";
                $values .= ", " . $val;
                $condition .= "AND  $key = ? ";
            }
            $count++;
        }
        $condition = $this->populateValues($condition, $data_values);
        $this->_query = "INSERT INTO $this->_table($fields) ";
        $this->_query .= "(SELECT $values FROM $this->_table ";
        $this->_query .= "WHERE (SELECT count(*) FROM $this->_table WHERE $condition) = 0 LIMIT 1) ";
        return $this;
    }

    public function prepareInsert($data) {
        $this->query = "";
        $dbfields = array();
        $insertdata = array();
        foreach ($data as $value) {
            if (empty($dbfields)) {
                foreach ($value as $index => $val) {
                    $dbfields[] = $index;
                }
            }
            break;
        }
        $this->query = "INSERT INTO $this->_table(" . implode(",", $dbfields) . ") ";
        foreach ($data as $key => $value) {
            $temp = array();
            foreach ($dbfields as $column) {
                $temp[] = $value[$column];
            }
            $temp = $this->SQLINJECTION($temp);
            $insertdata[$key] = " (" . implode(",", $temp) . ") ";
        }
        $vals = implode(",", $insertdata);
        $this->rawQuery($this->query . " VALUES " . $vals);
    }

    public function getAllInsertedIds($last_id, $limit = 1) {
        $this->_query = "SELECT $this->_primary_key FROM $this->_table WHERE $this->_primary_key > $last_id LIMIT $limit";
        return $this->execute()->fetchAll();
    }

    public function getLastRowId() {
        $this->_query = "SELECT $this->_primary_key FROM $this->_table ORDER BY $this->_primary_key DESC LIMIT 1";
        return $this->execute()->fetchFirst();
    }

    function rawQuery($query) {
        $this->_db->misc($query);
    }

    public function save() {
        unset($this->data['query']);
        if (isset($this->data) && !empty($this->data)) {
            $array = array();
            foreach ($this->data as $key => $value) {
                $array[$key] = $this->_db->mySQLSafe($value);
            }
        }
        if (key_exists($this->_primary_key, $this->data)) {
            $this->_db->update($this->_table, $array, "$this->_primary_key = " . $this->_db->mySQLSafe($this->data[$this->_primary_key]));
        } else {
            if (empty($this->data[$this->_primary_key])) {
                unset($array[$this->_primary_key]);
            }
            $this->data[$this->_primary_key] = $this->_db->insert($this->_table, $array);
        }
        return $this->data;
    }

    public function fetchFirst() {
        foreach ($this->collection as $row) {
            return $row;
        }
        return null;
    }

    public function fetchAll() {
        return $this->collection;
    }

    protected function createAssociateCollection($record) {
        $objectsArray = array();
        foreach ($record as $key => $value) {
            $temp[$this->_class] = $value;
            $objectsArray[] = $temp;
        }
        return $objectsArray;
    }

}

?>