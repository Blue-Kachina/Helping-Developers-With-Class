<?php
require_once(DIR_ROOT . '/lib/classes/coredb/classes/CoreFieldObject.php');

class CoreDB_mysql {

    private $_connect_error_message = 'Unable to connect to the database.';
    private $_conn = null;
    private $database;

    public function __construct($host, $database, $username, $password, $params = array(), $connectionInfo = array()) {
        if (!extension_loaded('mysqli')) {
            throw new CoreDBException('MYSQLi extension is not loaded. Please enable the extension in your .ini file!');
        }

        try {

            $conn = @mysqli_connect($host, $username, $password, $database);

						if(!$conn && defined('DB_HOST_FAILOVER') && DB_HOST_FAILOVER!='') {
							$conn = @mysqli_connect(DB_HOST_FAILOVER, $username, $password, $database);
						}

            if (!$conn || !is_a($conn, 'mysqli')) {
                throw new CoreDBException($this->_connect_error_message);
            }
            $this->database = $database;
            mysqli_set_charset($conn, 'utf8');
            $this->_conn = $conn;

        } catch (Exception $e) {
            $error = mysqli_connect_error();
            $this->_conn = null;
            throw new CoreDBException($e->getMessage() . '; Server Returned: ' . $error);
        }

    }

    public function __destruct() {
        if ($this->_conn) {
            mysqli_close($this->_conn);
        }
    }

    public function get_db_link() {
        return $this->_conn;
    }

    public function _query($sql) {
        $result = @mysqli_query($this->_conn, $sql);
        return $result;
    }

		public function _batchInsertQuery($table, $fields, $values) {
			$query = '
				insert into '.$this->_quoteTableName($table).'(
			';

			$sep = '';
			foreach($fields as $field) {
				$query.= $sep.$this->_quoteTableName($field);
				$sep = ',';
			}

			$query.=') Values ';

			$sep = '';
			foreach ($values as $valueBlock) {
				$query.=$sep.'('.implode(',', $valueBlock).')';
				$sep=',';
			}
			return $query;
		}

    public function _fetch($result, $row_format = CoreDB::FETCH_BOTH, $row_number = null) {
        switch ($row_format) {
            case CoreDB::FETCH_ASSOC:
                $row_format = MYSQLI_ASSOC;
                break;
            case CoreDB::FETCH_NUM:
                $row_format = MYSQLI_NUM;
                break;
            default:
                $row_format = MYSQLI_BOTH;
                break;
        }

        if (!$result) return false;

				if (!is_null($row_number) && $row_number<=(mysqli_num_rows($result)-1)) {
            mysqli_data_seek($result, $row_number);
        }

        $row = mysqli_fetch_array($result, $row_format);

        if (is_null($row)) return false;

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

    public function _fetchAll($result, $row_format = CoreDB::FETCH_BOTH, $keys = array()) {
        switch ($row_format) {
            case CoreDB::FETCH_ASSOC:
                $row_format = MYSQLI_ASSOC;
                break;
            case CoreDB::FETCH_NUM:
                $row_format = MYSQLI_NUM;
                break;
            default:
                $row_format = MYSQLI_BOTH;
                break;
        }

        if (!$result) return false;

        @mysqli_data_seek($result, 0);

				if(!count($keys)) {
					return mysqli_fetch_all($result, $row_format);
				}

        $data = array();

				while($row = mysqli_fetch_array($result, $row_format)) {
            $this->addKey($data, $keys, 0, $row);
        }

        return $data;
    }

    public function _fetch_object($result, $row_number) {
        if (!$result) return false;

        if (!is_null($row_number)) {
            mysqli_data_seek($result, $row_number);
        }

        $row = mysqli_fetch_object($result);

        if (is_null($row)) return false;

        return $row;
    }

    public function _close_result($result) {
        if ($result && is_a($result, 'mysqli_result')) {
					mysqli_free_result($result);
				}
				mysqli_next_result($this->get_db_link());
    }

    public function _insertID() {
        if ($this->_conn) return mysqli_insert_id($this->_conn);
        return null;
    }

    public function _affectedRows($result) {
        if ($result) {
            $affected = mysqli_affected_rows($this->_conn);
            if ($affected == 0) $affected = 1;
            return $affected;
        } else return -1;
    }

    public function _rowCount($result) {
        if ($result) {
            return mysqli_num_rows($result);
        } else return 0;
    }

    public function _concat($args) {
        return ' CONCAT(' . implode(', ', $args) . ') ';
    }

    public function _quote($value) {
        if (!is_null($value)) {
            if (is_a($this->_conn, 'mysqli')) {
                return '\'' . mysqli_real_escape_string($this->_conn, $value) . '\'';
            } else return '\'' . addslashes($value) . '\'';
        } else {
            return 'Null';
        }
    }

    public function _sqldate($format, $field) {
        $s = '';

        $len = strlen($format);
        for ($i=0; $i < $len; $i++) {
            $ch = $format[$i];
            switch ($ch) {
                case 'Y':
                    $s .= "%Y";
                    break;
                case 'y':
                    $s .= "%y";
                    break;
                case 'M':
                    $s .= "%M";
                    break;
                case 'm':
                    $s .= "%m";
                    break;
                case 'Q':
                case 'q':
                    $s .= "";
                    break;
                case 'D':
                    $s .= "%d";
                    break;
                case 'd':
                    $s .= "%d";
                    break;
                case 'h':
                    $s .= "%h";
                    break;
                case 'H':
                    $s .= "%H";
                    break;
                case 'i':
                    $s .= "%i";
                    break;
                case 's':
                    $s .= "%s";
                    break;
                case 'a':
                case 'A':
                    $s .= "%p";
                    break;

                default:
                    $s .= $ch;
                    break;
            }
        }
        return 'DATE_FORMAT(' . $field . ', \'' . $s . '\')';
    }

    public function _errorMsg() {
        $errors = mysqli_error($this->_conn);
        return $errors;
    }

    public function _errorCode() {
        $errors = mysqli_errno($this->_conn);
        return $errors;
    }

    public function _metaColumnNames($table) {
        $columns = array();
        $result = $this->_query("DESCRIBE $table");
        while ($row = $this->_fetch($result, CoreDB::FETCH_ASSOC)) {
            $columns[strtoupper($row['Field'])] = $row['Field'];
        }
        return $columns;
    }

    public function _metaColumns($table) {
        $columns = array();
        $result = $this->_query("DESCRIBE $table");
        while ($row = $this->_fetch($result, CoreDB::FETCH_ASSOC)) {
            if (strpos($row['Type'], '(') != false) {
                $type = substr($row['Type'], 0, strpos($row['Type'], '('));
            } else {
                $type = $row['Type'];
            }
            $length = substr($row['Type'], strpos($row['Type'], '(') + 1, strpos($row['Type'], ')') - strpos($row['Type'], '(') - 1);
            array_push($columns, new CoreFieldObject($row['Field'], $type, $length));
        }
        return $columns;
    }

    public function _beginTrans() {
        $this->_query('START TRANSACTION');
        return true;
    }

    public function _commitTrans($ok = true) {
        if ($ok) {
            $this->_query('COMMIT');
            return true;
        } else {
            $this->_rollbackTrans();
            return false;
        }
    }

    public function _rollbackTrans() {
        $this->_query('ROLLBACK');
        return true;
    }

    public function _metaTables($ttype = false) {
        switch ($ttype) {
            case 'TABLES':
                $sql = 'SHOW FULL TABLES IN ' . $this->database . " WHERE Table_type LIKE 'BASE TABLE'";
                //select only tables
                break;
            case 'VIEWS':
                $sql = 'SHOW FULL TABLES IN ' . $this->database . " WHERE Table_type LIKE 'VIEW'";
                //select only views
                break;
            default:
                $sql = 'SHOW FULL TABLES IN ' . $this->database;
                //select both tables and views
                break;
        }
        $rs = $this->_query($sql);
        $return = array();
        while ($row = $this->_fetch($rs, CoreDB::FETCH_NUM)) {
            $return[] = $row[0];
        }
        return $return;
    }

		public function _quoteTableName($value) {
			if(strpos($value, '`')!==false)
				return $value;
			else return '`'.$value.'`';
		}

}