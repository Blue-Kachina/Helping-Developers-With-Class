<?php
require_once(DIR_ROOT . '/lib/classes/coredb/classes/CoreFieldObject.php');

class CoreDB_sqlsrv {

    private $_connect_error_message = 'Unable to connect to the database.';
    private $_conn = null;
    private $rowCount = null;
    private $database;
    public $_version = null;
    private $_cursor = CoreDB::CURSOR_STATIC;

    public function __construct($host, $database, $username, $password, $params = array(), $connectionInfo = array()) {
        $this->database = $database;

        if(!extension_loaded('sqlsrv')) {
            throw new CoreDBException('Microsoft\'s SQLSRV extension is not loaded. Please download the extension from their website and load into your .ini file!');
        }

        try {

            if(count($connectionInfo) == 0) {
                // set defaults if none are provided
                $connectionInfo = array("UID" => $username, "PWD" => $password, "Database" => $database, 'ReturnDatesAsStrings' => true, 'CharacterSet' => 'UTF-8');
                // , "CharacterSet" => "UTF-8"
                if(defined('DB_HOST_FAILOVER') && DB_HOST_FAILOVER != '') {
                    $connectionInfo['Failover_Partner'] = DB_HOST_FAILOVER;
                }
            }

            $conn = sqlsrv_connect($host, $connectionInfo);

            if(!$conn) {
                throw new CoreDBException($this->_connect_error_message);
            }

            $this->_conn = $conn;

            $info = sqlsrv_server_info($conn);

            if(isset($info['SQLServerVersion'])) {
                $this->_version = $info['SQLServerVersion'];
            }

        } catch(Exception $e) {
            $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

            if(defined('ERRORS_ON') && ERRORS_ON==1) {
                print_r($errors);
                echo '<br/>';
                print_r($e->getMessage());
            }

            $this->_conn = null;
            throw new CoreDBException($e->getMessage() . '; Server Returned: ' . $errors[0][2]);
        }
    }

    public function __destruct() {
        if(is_resource($this->_conn)) {
            sqlsrv_close($this->_conn);
            $this->_conn = null;
        }
    }

    public function get_db_link() {
        return $this->_conn;
    }

    public function _query($sql, $params = array(), $cursor = CoreDB::CURSOR_STATIC) {
        $this->_cursor = $cursor;

        $substr = strtolower(substr($sql, 0, 6));

        if($substr == 'select' && !stristr($sql, 'SCOPE_IDENTITY()')) {

            $result = sqlsrv_query($this->_conn, $sql, $params, array('Scrollable' => $cursor));

        } elseif($substr == 'insert') {
            $result = sqlsrv_query($this->_conn, $sql . '; SELECT SCOPE_IDENTITY() AS insertID', $params);
        } elseif($substr == 'begin ') {
            $result = sqlsrv_begin_transaction($this->_conn);
        } elseif($substr == 'commit') {
            $result = sqlsrv_commit($this->_conn);
        } elseif($substr == 'rollba') {
            $result = sqlsrv_rollback($this->_conn);
        } else {
            $result = sqlsrv_query($this->_conn, $sql, $params);
        }

        if(!$result) {
            $this->_connect_error_message = sqlsrv_errors();
        }

        return $result;
    }

    public function _batchInsertQuery($table, $fields, $values) {
        if(!count($fields) || !count($values)) return '';
        $query = '
					insert into ' . $this->_quoteTableName($table) . '(
				';

        $sep = '';
        foreach($fields as $field) {
            $query .= $sep . $this->_quoteTableName($field);
            $sep = ',';
        }

        $query .= ') Values ';

        $sep = '';
        foreach($values as $valueBlock) {
            $query .= $sep . '(' . implode(',', $valueBlock) . ')';
            $sep = ',';
        }
        return $query;
    }

    public function _fetch($result, $row_format = CoreDB::FETCH_BOTH, &$rowPointer = null) {
        if(!is_resource($result)) {
            return false;
        }

        if($this->_cursor != CoreDB::CURSOR_FORWARD) {
            $row = sqlsrv_fetch_array($result, $row_format, SQLSRV_SCROLL_ABSOLUTE, $rowPointer);
            $rowPointer++;
        } else {
            $row = sqlsrv_fetch_array($result, $row_format);
            $rowPointer++;
        }


        if(is_null($row)) {
            return false;
        }

        return $row;
    }

    private function addKey(&$currentRow, $keys, $current_index, $value) {
        if($current_index < count($keys)) {
            if($keys[$current_index] === '[]') {
                $next_row =& $currentRow[array_push($currentRow, array()) - 1];
            } else {
                if(!isset($currentRow[$value[$keys[$current_index]]])) {
                    $currentRow[$value[$keys[$current_index]]] = array();
                }
                $next_row =& $currentRow[$value[$keys[$current_index]]];
            }
            $this->addKey($next_row, $keys, $current_index + 1, $value);
        } else {
            $currentRow = $value;
        }
    }

    public function _fetchAll($result, $row_format = CoreDB::FETCH_BOTH, $keys = array(), $page=0, $amount_per_page=20, &$row_pointer=null) {
        $data = array();

        if(!is_resource($result)) {
            return $data;
        }


        if(!$keys) {
            while($row = $this->_fetch($result, $row_format, $row_pointer)) {
                $data[] = $row;
                if($page > 0 && $amount_per_page > 0 && $row_pointer >= ($page * $amount_per_page)) {
                    return $data;
                }
            }

        } else {
            while($row = $this->_fetch($result, $row_format, $row_pointer)) {
                $this->addKey($data, $keys, 0, $row);
                if($page > 0 && $amount_per_page > 0 && $row_pointer >= ($page * $amount_per_page)) {
                    return $data;
                }
            }

        }

        return $data;
    }

    public function _fetch_object($result, $row_number) {
        if(!is_resource($result)) {
            return false;
        }
        if(!is_null($row_number)) {
            if($this->rowPointer < $row_number) {
                while($this->rowPointer++ < $row_number) {
                    sqlsrv_fetch_array($result);
                }
            }
            $object = sqlsrv_fetch_object($result, 'stdClass', array(), SQLSRV_SCROLL_ABSOLUTE, $row_number);
            $this->rowPointer++;
        } else {
            $object = sqlsrv_fetch_object($result);
        }

        if(is_null($object)) {
            return false;
        }

        return $object;
    }

    public function _close_result($result) {
        if(is_resource($result)) {
            sqlsrv_free_stmt($result);
        }
    }

    public function _insertID($result) {
        if($this->_conn) {
            if(is_resource($result)) {
                sqlsrv_next_result($result);
                sqlsrv_fetch($result);
                return sqlsrv_get_field($result, 0);
            } else return 0;
        }
        return 0;
    }

    public function _affectedRows($result) {
        if(is_resource($result)) {
            return sqlsrv_rows_affected($result);
        } else return 0;
    }

    public function _rowCount($result) {
        if(is_resource($result)) {

            if(!is_null($this->rowCount)) {
                return $this->rowCount;
            } else return sqlsrv_num_rows($result);

        } else return 0;
    }

    public function _setRowCount($count) {
        $this->rowCount = $count;
    }

    public function _concat($args) {
        return implode(' + ', $args);
    }

    public function _quote($value) {
        return '\'' . str_replace("'", "''", $value) . '\'';
    }

    public function _sqldate($format, $field) {
        $s = '';

        $len = strlen($format);
        for($i = 0; $i < $len; $i++) {
            if(!empty($s)) {
                $s .= '+';
            }
            $ch = $format[$i];
            switch($ch) {
                case 'Y':
                case 'y':
                    $s .= "datename(yyyy,$field)";
                    break;
                case 'M':
                    $s .= "convert(char(3),$field,0)";
                    break;
                case 'm':
                    $s .= "replace(str(month($field),2),' ','0')";
                    break;
                case 'Q':
                case 'q':
                    $s .= "datename(quarter,$field)";
                    break;
                case 'D':
                case 'd':
                    $s .= "replace(str(day($field),2),' ','0')";
                    break;
                case 'h':
                    $s .= "substring(convert(char(14),$field,0),13,2)";
                    break;
                case 'H':
                    $s .= "replace(str(datepart(hh,$field),2),' ','0')";
                    break;
                case 'i':
                    $s .= "replace(str(datepart(mi,$field),2),' ','0')";
                    break;
                case 's':
                    $s .= "replace(str(datepart(ss,$field),2),' ','0')";
                    break;
                case 'a':
                case 'A':
                    $s .= "substring(convert(char(19),$field,0),18,2)";
                    break;

                default:
                    if($ch == '\\') {
                        $i++;
                        $ch = substr($format, $i, 1);
                    }
                    $s .= $this->_quote($ch);
                    break;
            }
        }
        return $s;
    }

    public function _errorMsg() {
        $errors = sqlsrv_errors();
        if(isset($errors[0][2])) {
            return $errors[0][2];
        }
    }

    public function _errorCode() {
        $errors = sqlsrv_errors();
        if(isset($errors[0][1])) {
            return $errors[0][1];
        }
    }

    public function _metaColumnNames($table) {
        $columns = array();
        $params  = Array(Array($table, SQLSRV_PARAM_IN));
        $result  = $this->_query("{call sp_columns(?)}", $params);
        //sqlsrv_next_result($result);
        while($row = $this->_fetch($result, CoreDB::FETCH_ASSOC)) {
            $columns[strtoupper($row['COLUMN_NAME'])] = $row['COLUMN_NAME'];
        }
        return $columns;
    }

    public function _metaColumns($table) {
        $columns = array();
        $params  = Array(Array($table, SQLSRV_PARAM_IN));
        $result  = $this->_query("{call sp_columns(?)}", $params);
        //sqlsrv_next_result($result);
        while($row = $this->_fetch($result, CoreDB::FETCH_ASSOC)) {
            $type     = $row['TYPE_NAME'];
            $identity = 'identity';
            if(strlen($type) > 9 && strpos($type, $identity) != false) {
                if(substr($type, strpos($type, $identity)) == $identity) {
                    $type = substr($type, 0, strlen($type) - strlen($identity) - 1);
                }
            }
            array_push($columns, new CoreFieldObject($row['COLUMN_NAME'], $type, $row['LENGTH']));
        }
        return $columns;
    }

    public function _beginTrans($transationName = '') {
        $this->_query('BEGIN TRAN ' . $transationName);
        return true;
    }

    public function _commitTrans($ok = true, $transationName = '') {
        if($ok) {
            $this->_query('COMMIT TRAN ' . $transationName);
            return true;
        } else {
            $this->_rollbackTrans($transationName);
            return false;
        }
    }

    public function _rollbackTrans($transationName = '') {
        $this->_query('ROLLBACK TRAN ' . $transationName);
        return true;
    }

    public function _metaTables($ttype = false) {
        switch($ttype) {
            case 'TABLES':
                $sql = 'SELECT name FROM sys.Tables';
                break;
            case 'VIEWS':
                throw new Exception('Metatables for views in mssql has not been fully implemented.');
                //select only views
                break;
            default:
                throw new Exception('Metatables for both views and tables in mssql has not been fully implemented.');
                //select both tables and views
                break;
        }
        $rs     = $this->_query($sql);
        $return = array();
        while($row = $this->_fetch($rs, CoreDB::FETCH_NUM)) {
            $return[] = $row[0];
        }
        return $return;
    }

    public function _quoteTableName($value) {
        if(strpos($value, '[') !== false || strpos($value, ']') !== false) {
            return $value;
        } else return '[' . $value . ']';
    }
}

?>
