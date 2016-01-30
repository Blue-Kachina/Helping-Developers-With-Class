<?php

class CoreDB {

    private $_link = null;
    private $db = null;
    public $profiling = false;
    public $profiling_results = array();
    public $queryErrors = 0;
    private $profile_start_time; // microsecs

    private $last_result = null; //This is the most recent result set from the query method, saved for Affected_Rows()

    private $insert_id = null;

    const FETCH_NUM   = 1;
    const FETCH_ASSOC = 2;
    const FETCH_BOTH  = 3;

    const CURSOR_KEYSET = 'keyset';
    const CURSOR_STATIC = 'static';
    const CURSOR_FORWARD = 'forward';
    const CURSOR_BUFFERED = 'buffered';



    public function __construct($driver = 'mysqli', $host = '', $database = '', $username = '', $password = '', $params = array()) {
        if(defined('COREDB_PROFILER') && COREDB_PROFILER) {
            $this->profiling          = true;
            $this->profile_start_time = microtime(true);
        }

        $driver_file = DIR_ROOT . '/lib/classes/coredb/drivers/' . $driver . '.php';
        if(file_exists($driver_file)) {
            include_once($driver_file);
        } else {
            throw new CoreDBException('Driver ' . $driver . ' not found.');
        }

        $driver_class = 'CoreDB_' . $driver;
        $this->db     = new $driver_class($host, $database, $username, $password, $params = array());

        $this->_link = $this->db->get_db_link();

    }


    public function printProfilingInfo() {
        if(defined('PROFILER_NOSHOW')) {
            return;
        }
        echo '<br/><pre style="color: #333; background-color: #fffff3; border: 1px solid #000; padding: 2px; white-space: pre-wrap;"><fieldset><legend><strong>CoreDB Profiling Results</strong></legend>';
        print_r($this->profiling_results);
        echo '</fieldset></pre>';
        if($this->queryErrors) {
            echo '
				<script type="text/javascript">
					var alrt = document.getElementById(\'critalert\');

					if(alrt) {
						alrt.innerHTML = \'<h3 style="color: red; background-color: #ffe; border: 2px solid black; padding: 3px; margin: 0;">Attention: There are Query Errors Below. They will be marked in red. File also saved to: ' . str_replace('\\', '/', DIR_ROOT) . '/debugOut.html</h3>\';
						alrt.style.display = \'inline\';
					}
				</script>
				';
        }
    }

    public function batchInsert($table, $fields, $values) {
        if(!count($fields) || !count($values)) return 0;

        return $this->query($this->db->_batchInsertQuery($table, $fields, $values));
    }

    public function prepare($query) {
        $sql = trim($query);

        if(empty($sql)) {
            debugOut('<strong style="color: red;">Query cannot be empty. </strong><br/><br/>Trace: <br/><br/>' . var_export(debug_backtrace(false), true));
        }
        $result = null;
        $idx    = count($this->profiling_results);
        if($this->_link) {
            try {
                if($this->profiling) {
                    $this->profiling_results[$idx] = array('Query' => $sql, 'Time Since Connection' => sprintf('%0.2f', (microtime(true) - $this->profile_start_time)));
                    $trace                         = debug_backtrace(false);
                    foreach($trace as $id => $data) {
                        if(isset($data['file'])) {
                            $this->profiling_results[$idx]['File' . $id] = $data['file'];
                        }
                        if(isset($data['line'])) {
                            $this->profiling_results[$idx]['Line' . $id] = $data['line'];
                        }
                        if(isset($data['function'])) {
                            $this->profiling_results[$idx]['Function' . $id] = $data['function'];
                        }
                    }
                }

                $result = $this->db->_prepare($sql);
                if(!$result) {
                    $this->queryErrors = true;
                    if($this->profiling) {
                        $this->profiling_results[$idx]['Result:'] = '<span style="color: red;">Failed: ' . $this->errorMsg() . '</span>';
                    }
                    return false;
                }
                $this->last_result = $result;

                if($this->profiling) {
                    $this->profiling_results[$idx]['Result:'] = 'Success';
                }

                return new CoreDB_Recordset($result, $this->db, null, null);
            } catch(Exception $e) {
                if($this->profiling) {
                    $this->profiling_results[$idx]['Result:'] = '<span style="font-color: red;">Failed: ' . $this->errorMsg() . '</span>';
                }
                throw new CoreDBException('There was an error performing a query against the database.');
            }
        } else {
            throw new CoreDBException('There is no database connection. Query cannot be completed.');
        }
    }

    /**
     * @param $query
     * @param null $page
     * @param null $amount_per_page
     * @param array $params
     * @param string $cursor
     * @throws CoreDBException
     * @return CoreDB_Recordset | null
     */
    public function query($query, $page = null, $amount_per_page = null, $params = array(), $cursor = self::CURSOR_STATIC) {
        $sql = ltrim($query);

        if(empty($sql)) {
            return null;
        }
        $result = null;
        $idx    = count($this->profiling_results);
        if($this->_link) {
            try {
                if($this->profiling && !empty($sql)) {
                    $this->profiling_results[$idx] = array('Query' => preg_replace('/( +|\t)/',' ', $sql)."\r\n", 'Time Since Connection' => sprintf('%0.2f', (microtime(true) - $this->profile_start_time)));
                    $trace = debug_backtrace(false);
                    foreach($trace as $id => $data) {
                        if(isset($data['file'])) {
                            $this->profiling_results[$idx]['File' . $id] = $data['file'];
                        }
                        if(isset($data['line'])) {
                            $this->profiling_results[$idx]['Line' . $id] = $data['line'];
                        }
                        if(isset($data['function'])) {
                            $this->profiling_results[$idx]['Function' . $id] = $data['function'];
                        }
                    }
                }

                $result = $this->db->_query($sql, $params, $cursor);

                if(!$result && !empty($sql)) {

                    ob_start();
                    print_r(debug_backtrace(false));
                    $backtrace = array('data'=>preg_replace('/( +|\t)/', ' ', ob_get_contents()), 'file_name'=>'backtrace.txt');
                    ob_end_clean();

                    ob_start();
                    phpinfo();
                    $phpinfo = array('data'=>ob_get_contents(), 'file_name'=>'phpinfo.html');
                    ob_end_clean();

                    if(defined('RUN_MODE') && RUN_MODE=='production') {
                        //sendEmail('rcraig@coresolutions.ca', 'PD Place Database Error from URL: ' . $_SERVER['SERVER_NAME'] . '/' . $_SERVER['URL'], 'Error Message: ' . $this->db->_errorCode() . ' ::: ' . $this->db->_errorMsg() . '<br/><br/>Query:<br/>' . $sql, null, array($backtrace, $phpinfo));
                    }

                    // write to failed query log:
                    if($fp = fopen(DIR_ROOT.'/logs/failed_query_log_'.date('Y-m-d').'.txt', 'a')) {
                        fwrite($fp, str_pad($s='', 200, '*') . "\r\n");
                        fwrite($fp, "Time: ".(date('Y-m-d H:i:s'))."\r\n\r\n");
                        fwrite($fp, "Error: \r\n".$this->db->_errorMsg()."\r\n\r\n");
                        fwrite($fp, "Trace: \r\n".$backtrace['data']."\r\n");
                        fwrite($fp, "\r\n");
                        fclose($fp);
                    }

                    $this->queryErrors = true;
                    if($this->profiling) {
                        $this->profiling_results[$idx]['Result:'] = '<span style="color: red;">Failed: ' . $this->db->_errorMsg() . '</span>';
                    }

                    //throw new CoreDBException($this->db->_errorMsg(), $this->db->_errorCode());

                    return false;
                }

                //if($result && !sqlsrv_has_rows($result)) return 0;

                $this->last_result = $result;
                $this->insert_id = null;

                if($this->profiling) {
                    $this->profiling_results[$idx]['Result:'] = 'Success';
                }

                $rs = new CoreDB_Recordset($result, $this->db, $page, $amount_per_page);

                return $rs;
            } catch(Exception $e) {
                if($this->profiling) {
                    $this->profiling_results[$idx]['Result:'] = '<span style="font-color: red;">Failed: ' . $this->errorMsg() . '</span>';
                }
                throw new CoreDBException($this->db->_errorMsg(), $this->db->_errorCode());
            }
        } else {
            throw new CoreDBException('There is no database connection. Query cannot be completed.');
        }
    }

    public function rowCountQuery($query) {
        $rs = $this->query($query);

        if($rs) {
            $this->setRowCount($rs->fetchSingleValue());
        }
    }


    public function SelectLimit($sql, $amount_per_page = 20, $start_record = 0) {
        return $this->query($sql, floor($start_record / $amount_per_page) + 1, $amount_per_page);
    }

    public function SQLDate($format, $field) {
        return $this->db->_sqldate($format, $field);
    }

    public function Concat() {
        $args = func_get_args();
        return $this->db->_concat($args);
    }

    public function quote($value) {
        return $this->db->_quote($value);
    }

    /**
     * Used when a single value from a single row is needed. Can pick index - defaults to 0
     * @param string $query
     * @param int $index
     * @return mixed
     */
    public function getSingleValue($query, $index = 0) {
        $value = null;
        $rs    = $this->query($query, 1, 1);
        if($rs && $rs->rowCount() > 0) {
            $row = $rs->fetch(CoreDB::FETCH_NUM);
            if(isset($row[$index])) {
                $value = $row[$index];
            }
        }
        return $value;
    }

    public function numEscape($value, $useNull = true) {
        // if not numeric then either write a 0 or NULL - default is to write NULL
        $value = trim($value);

        if(strtolower($value) == 'null') {
            return $value;
        }

        if(!is_numeric($value)) {
            if($useNull) {
                $value = 'NULL';
            } else {
                $value = 0;
            }
        }
        return $value;
    }

    public function boolNumEscape($value) {
        $value = trim($value);

        if($value != 1) {
            $value = 0;
        }
        return $value;
    }

    public function dateEscape($value, $useNull = true, $withTime = true) {
        $value = trim($value);

        if(@!strtotime($value)) {
            if($useNull) {
                return 'NULL';
            } else {
                return '';
            }
        } else {
            if($withTime) {
                return $this->quote(date('Y-m-d H:i:s', strtotime($value)));
            } else {
                return $this->quote(date('Y-m-d', strtotime($value)));
            }
        }
    }

    public function quoteTableName($value) {
        return $this->db->_quoteTableName($value);
    }

    public function insertID() {
        if(is_null($this->insert_id)) {
            $this->insert_id = $this->db->_insertID($this->last_result);
        }
        return $this->insert_id;
    }

    public function errorMsg() {
        return $this->db->_errorMsg();
    }

    public function ErrorInfo() {
        return $this->db->_errorMsg();
    }

    public function errorCode() {
        return $this->db->_errorCode();
    }

    public function get_db_resource() {
        return $this->_link;
    }

    public function hasResult($result) {
        if($result && $result instanceof CoreDB_Recordset) {
            return true;
        } else return false;
    }

    public function MetaColumnNames($table) {
        return $this->db->_metaColumnNames($table);
    }

    public function MetaColumns($table) {
        return $this->db->_metaColumns($table);
    }

    public function MetaTables($ttype = false) {
        return $this->db->_metaTables($ttype);
    }

    public function Affected_Rows() {
        return $this->db->_affectedRows($this->last_result);
    }

    public function setRowCount($count) {
        $this->db->_setRowCount($count);
    }

    public function DBDate($date) {
        return $this->SQLDate('Y-m-d H:i:s', $date);
    }

    public function BeginTrans() {
        return $this->db->_beginTrans();
    }

    public function CommitTrans($ok = true) {
        return $this->db->_commitTrans($ok);
    }

    public function RollbackTrans() {
        return $this->db->_rollbackTrans();
    }

    public function strNullOnEmpty($str) {
        if(empty($str)) {
            return 'NULL';
        } else {
            return $this->quote($str);
        }
    }

    public function numNullOnEmpty($int, $treatZeroAsNull = false) {
        if(!is_numeric($int)) {
            return 'NULL';
        }
        if($treatZeroAsNull && is_numeric($int) && !$int) {
            return 'NULL';
        }

        return $int;
    }



}

class CoreDB_Recordset {
    private $resultSet = null;
    private $db = null;
    private $_page = 1;
    private $_amount_per_page = 20;
    private $current_record = null;
    private $rowPointer = null;
    public $totalRecords = 0;

    public function __construct($resultSet, $db, $page = null, $amount_per_page = null) {
        $this->resultSet        = $resultSet;
        $this->db               = $db;
        $this->_page            = $page;
        $this->_amount_per_page = $amount_per_page;
        if($page > 0 && $amount_per_page > 0) {
            $this->current_record = ($page - 1) * $amount_per_page;
            $this->rowPointer     = 0;
        }

        if(!$page && $amount_per_page > 0) {
            $this->_page          = 1;
            $this->current_record = 0;
            $this->rowPointer     = 0;
        }
    }

    public function __destruct() { $this->Close(); }

    /**
     * @param int $row_format
     * @return array|bool
     */
    public function fetch($row_format = CoreDB::FETCH_BOTH) {
        if($this->_page > 0 && $this->_amount_per_page > 0 && $this->current_record >= ($this->_page * $this->_amount_per_page)) {
            return false;
        }
        $fetched = $this->db->_fetch($this->resultSet, $row_format, $this->current_record);


        return $fetched;
    }

    public function fetchSingleValue() {
        $value = '';
        $row = $this->fetch(CoreDB::FETCH_NUM);
        if(isset($row[0])) $value = $row[0];
        return $value;
    }

    public function fetchAll($row_format = CoreDB::FETCH_BOTH, $keys = array()) {
        return $this->db->_fetchAll($this->resultSet, $row_format, $keys, $this->_page, $this->_amount_per_page, $this->current_record);
    }

    public function fetchKeyValuePairs() {
        $list = array();
        while($row = $this->fetch(CoreDB::FETCH_NUM)) {
            if(isset($row[1])) {
                $list[$row[0]] = $row[1];
            }
        }
        return $list;
    }

    public function FetchNextObj() {
        if($this->_page > 0 && $this->_amount_per_page > 0 && $this->current_record >= ($this->_page * $this->_amount_per_page)) {
            return false;
        }
        $fetched = $this->db->_fetch_object($this->resultSet, $this->current_record);

        if(!is_null($this->current_record)) {
            $this->current_record++;
        }

        return $fetched;
    }

    public function Move($row_number) {
        $this->current_record = $row_number;
    }

    public function rows_returned() {
        $rows_returned = 0;
        if($this->resultSet) {
            $rows_returned = ($this->_amount_per_page > 0 && $this->_amount_per_page < $this->rowCount()) ? $this->_amount_per_page : $this->rowCount();
        }

        return $rows_returned;
        //return $this->db->_rowCount($this->resultSet);
    }

    public function rowCount() {
        return $this->db->_rowCount($this->resultSet);
    }

    public function setRowCount($count) {
        $this->db->_setRowCount($count);
    }

    public function affectedRows() {
        return $this->db->_affectedRows($this->resultSet);
    }

    public function Close() {
        $this->db->_close_result($this->resultSet);
        $this->resultSet = null;
    }
}

class CoreDBQuery {
    public function __construct($properties = array()) { return $this; }
    public function __destruct() {}

    private $query = array();
    static private $level = 0;

    /** INNER JOIN */
    const JOIN_INNER = 'INNER';
    /** LEFT JOIN */
    const JOIN_LEFT  = 'LEFT';
    /** CROSS JOIN */
    const JOIN_CROSS = 'CROSS';

    /**
     * @param array|string $fields
     * @return CoreDBQuery
     */
    public function select($fields = array()) {
        if(!is_array($fields) && !empty($fields)) $fields = [$fields];
        $this->query[] = ' SELECT '.implode(', ', $fields);
        return $this;
    }

    /**
     * @param string $as
     * @return CoreDBQuery
     */
    public function alias($as) {
        $this->query[] = ' AS '.$as;
        return $this;
    }

    /**
     * @param string $table
     * @param array|null $fields
     * @param array $values
     * @param mixed $params
     * @return CoreDBQuery
     */
    public function insert($table, $fields, $values, $params = array()) {
        $this->query[]=" INSERT INTO $table ".'('.implode(', ', $fields).') Values ('.implode(', ', $values).')';
        return $this;
    }

    /**
     * @param string $table
     * @param array $fields
     * @param array $values
     * @param mixed $params
     * @return CoreDBQuery
     */
    public function update($table, $fields, $values, $params = array()) {
        return $this;
    }

    /**
     * @param string $s
     * @return CoreDBQuery
     */
    public function delete($s='') {
        return $this;
    }

    /**
     * @param string $table
     * @return CoreDBQuery
     */
    public function from($table) {
        return $this;
    }

    /**
     * @param string $s
     * @param string $type
     * @return CoreDBQuery
     */
    public function join($s, $type='INNER') {
        return $this;
    }

    /**
     * @param string $s
     * @return CoreDBQuery
     */
    public function groupby($s) {
        return $this;
    }

    /**
     * @param array|string $s
     * @param int $offset
     * @param int|null $fetch
     * @return CoreDBQuery
     */
    public function orderby($s, $offset=0, $fetch=null) {
        return $this;
    }

    /**
     * @param $s
     * @param mixed $params
     * @return $this
     */
    public function where($s, $params = array()) {
        return $this;
    }

    /**
     * @param string $s
     * @param mixed $params
     * @return CoreDBQuery
     */
    public function having($s, $params = array()) {
        return $this;
    }

}

class CoreDBException extends Exception {
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}