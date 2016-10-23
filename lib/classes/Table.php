<?php
abstract class Table {

    const FILTER_TYPE_NONE = 0;
    const FILTER_TYPE_BOOL = 1;
    const FILTER_TYPE_INT = 2;
    const FILTER_TYPE_FLOAT = 3;
    const FILTER_TYPE_STRING = 4;

    const ARRAY_TYPE_NUMERIC = 1;
    const ARRAY_TYPE_ASSOC = 2;
    const ARRAY_TYPE_BOTH = 3;



    protected $offset = "";
    protected $order = "";
    protected $search = "";
    public $resultTotal = 0;
    public $filteredTotal = 0;
    public $is_session = 0;

    public function loadFromArray($row) {
        foreach($row as $key => $value){
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }
    }

    /**
     * @purpose To add an Order By statement that will be used in SQL. Used for datatables
     * @param $order array - array that contains condition(asc/desc) and the value (order by)
     * @author David Leonard
     */
    public function setOrderBy($order){
        $this->order = 'ORDER BY ';
        if(!empty($order)) {
            $this->order .= $order['value'] . ' ' . $order['condition'];
        }
        $_SESSION['order_by_val'] = $this->order;
    }

    /**
     * @purpose This will be used to limit the amount of results returned in SQL. Used for datatables
     * @param $start int - the starting position to grab results from table
     * @param $length int - how many results to show
     * @author David Leonard
     */
    public function setOffset($start, $length) {
        if(!is_null($start) && !is_null($length)) {
            $this->offset = 'LIMIT ';
            $this->offset .= $length . ' OFFSET ' . $start . ' ';
        }
    }

    /**
     * @purpose This will be used to filter the SQL search
     * @param $value string - value given by the user to filter the datatable
     * @param $column array - an array of columns that would be searched on
     * @author David Leonard
     */
    public function setSearch($value, $column) {
        $this->search .= "(";
        for($i=0; $i < sizeof($column); ++$i) {
            $search = "";
            $search = $column[$i]. ' LIKE \'%' . $value . '%\'';
            $i+1 == sizeof($column) ? $this->search .= $search : $this->search .= $search . ' OR ';
        }
        $this->search .= ")";
    }

    public function setAdvSearch($array){
        $sorted = [];

        if($array[0]['name'] != 'search_type') {
            $x = 0;
            //Sort array into 1 object per search param, it is size of array - 1 because there is an extra field we do not need
            for ($i = 0; $i < (sizeof($array) - 1); $i++) {
                if ($i % 3 == 0)
                    $sorted[$x]['field'] = $array[$i]['value'];
                else if ($i % 3 == 1)
                    $sorted[$x]['operator'] = $array[$i]['value'];
                else if ($i % 3 == 2) {
                    $sorted[$x]['value'] = $array[$i]['value'];
                    $x++;
                }
            }

            $this->search = '';
            for ($i = 0; $i < sizeof($sorted); $i++) {
                $sorted[$i]['field'] = str_replace('\'', '', $sorted[$i]['field']);

                if ($sorted[$i]['operator'] != '0') {
                    if ($sorted[$i]['operator'] == 'LIKE') {
                        $search = $sorted[$i]['field'] . ' ' . $sorted[$i]['operator'] . ' \'%' . $sorted[$i]['value'] . '%\'';
                    } else {
                        $search = $sorted[$i]['field'] . ' ' . $sorted[$i]['operator'] . ' \'' . $sorted[$i]['value'] . '\'';
                    }
                } else {
                    //horrible, horrible, horrible fix for one specific use
                    if($sorted[$i]['field'] == "[user].zk_agencyID_f") {
                        $sorted[$i]['field'] = "[user_classroom].zk_agencyID_f";
                    }

                    //foreign key lookup
                    $search = $sorted[$i]['field'] . ' = ' . ' \'' . $sorted[$i]['value'] . '\'';
                }

                $this->search .= (($i + 1 == sizeof($sorted)) ?  $search : ($search . ' AND '));
            }
        } else {
            array_shift($array);
            if ($this->search != '') {
                $this->search .= ' AND ';
            }
            foreach($array as $search) {
                if($search['value'] != "all") {
                    $this->search .= $search['name']. ' = ' . ' \''. $search['value'] .'\' AND ';
                }
            }

            if( ( $pos = strrpos( $this->search , ' AND ' ) ) !== false ) {
                $search_length  = strlen( ' AND ' );
                $this->search = substr_replace( $this->search , '' , $pos , $search_length );
            }

        }
    }

    public function clearSearch() {
        $this->search = "";
    }

    /**
     * @purpose This function is used for printing functionality, it will create a session variable that will
     * contain all the needed values to produce a datatable with the same results as the search table
     * @param $row_array - Array that contains reuslts of the datatable, this is used in print previews
     * @author David Leonard
     */
    public function setSession($row_array) {
        unset($_SESSION["datatable_values"]);
        $_SESSION["datatable_values"] = $row_array;
    }

    /**
     * @param $primary_key string - column name you wish to count
     * @param $from string - table(s) name(s) used for the from statement
     * @param $join string - any join statement needed
     * @param $distinct boolean - determines if you want to count the number of distinct values of the primary key
     * @return int - returns total number of rows for the sql
     * @throws CoreDBException
     * @author David Leonard
     */
    public function getTableListTotal($primary_key, $from, $join="", $where = '', $distinct = true) {
        $db = get_db_connection();
        if ($distinct) {
            $sql = 'SELECT COUNT(DISTINCT ' . $primary_key . ') AS count FROM ' . $from . ' ' . $join . ' WHERE 1=1 ' . $where;
        } else {
            $sql = 'SELECT COUNT(' . $primary_key . ') AS count FROM ' . $from . ' ' . $join . ' WHERE 1=1 ' . $where;
        }

        $rs = $db->query($sql);

        if($rs) {
            $row = $rs->fetch(CoreDB::FETCH_ASSOC);
            return $row['count'];
        }
        else return 0;
    }

    /**
     * @param $primary_key string - column name you wish to count
     * @param $from string - table(s) name(s) used for the from statement
     * @param $join string - any join statement needed
     * @param $where string - where if you need to customize the stmt
     * @param $distinct boolean - determines if you want to count the number of distinct values of the primary key
     * @return int - returns filtered total number of rows for the sql (added where statement)
     * @throws CoreDBException
     * @author David Leonard
     */
    public function getTableListFilteredTotal($primary_key, $from, $join="", $addWhere="", $distinct = true) {
        $where = "";
        if($addWhere == "")  {
            if($this->search != "") {
                $where = ' AND '. $this->search;
            }
        } else {
            $where = $addWhere;
            if($this->search != "") {
                $where  .= ' AND ('. $this->search .') ';
            }
        }
        $db = get_db_connection();
        if($distinct) {
            $sql = 'SELECT COUNT(DISTINCT ' . $primary_key . ') AS count FROM ' . $from . ' ' . $join . ' WHERE 1=1 ' . $where;
        } else {
            $sql = 'SELECT COUNT(' . $primary_key . ') AS count FROM ' . $from . ' ' . $join . ' WHERE 1=1 ' . $where;
        }
        $rs = $db->query($sql);

        if($rs) {
            $row = $rs->fetch(CoreDB::FETCH_ASSOC);
            return $row['count'];
        }
        else return 0;
    }


    /**
     * This function is to be used when a MySQL database is the source of data within inherited classes generated by "Helping-Developers-With-Class"
     * It returns bound parameter types to be used to virtually accomplish parameterized querying
     * @param string $listOfFields
     * @return string
     */
    public function GetBoundParamTypeString($listOfFields='*')
    {
        if ($listOfFields == '*')
            $listOfFields = $this->allFieldsWithoutKeys;
        $myMeta = $this->GetTableMetaAsAssocArray();
        $boundParamString = '';
        foreach ($listOfFields as $field) {
            if (array_key_exists($field, $myMeta)) {
                $boundParamString .= $myMeta[$field]['BOUND_PARAM_TYPE'];
            }
        }
        return $boundParamString;
    }


    /**
     * This function is primarily only invoked privately within inherited classes generated by "Helping-Developers-With-Class"
     * Its primary purpose is to return a list of values when given a list of field names
     * It contains a number of options that can be set via parameters
     * @param string $listOfFields
     * @param int $arrayType
     * @param bool $boolUseSanitizeFilters
     * @param bool $boolEncapsulateInQuotes
     * @param bool $boolIncludeEmpties
     * @param bool $boolIncludeNulls
     * @return array
     */
    public function GetArrayOfFieldValues($listOfFields='*', $arrayType=test::ARRAY_TYPE_ASSOC, $boolUseSanitizeFilters=false, $boolEncapsulateInQuotes=false, $boolIncludeEmpties=true, $boolIncludeNulls=true){
        if ($listOfFields=='*')
            $listOfFields=$this->allFieldsWithoutKeys;
        $tableMeta=$this->GetTableMetaAsAssocArray();
        $result = array();
        $i = -1;
        foreach ($listOfFields as $myIndex=>$fieldName) {
            if (property_exists($this, $fieldName)) {
                $myValue=$this->$fieldName;
                $myMeta=$tableMeta[$fieldName];
                $boolIsNull = is_null($myValue);
                $boolIsEmpty = ( isset($myValue) && empty($myValue) ) && ( $myValue !== FALSE && $myValue !== 0 && $myValue !== 0.0 && $myValue !== array() );
                $boolExcludeMe = (!$boolIncludeEmpties && $boolIsEmpty) || (!$boolIncludeNulls && $boolIsNull);
                if(!$boolExcludeMe){
                    $i++;
                    if($arrayType==$this::ARRAY_TYPE_ASSOC || $arrayType==$this::ARRAY_TYPE_BOTH){
                        $result[$fieldName]=$this->ReturnFormattedData($myValue,$myMeta,$boolUseSanitizeFilters,$boolEncapsulateInQuotes);
                    }
                    if($arrayType==$this::ARRAY_TYPE_NUMERIC || $arrayType==$this::ARRAY_TYPE_BOTH){
                        $result[$i]=$this->ReturnFormattedData($myValue,$myMeta,$boolUseSanitizeFilters,$boolEncapsulateInQuotes);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * This function is used for sanitizing data within inherited classes generated by "Helping-Developers-With-Class"
     * It probably won't get used much since parameterized queries are now in effect
     * It could probably use some more work if it is going to be used too
     * @param $data
     * @param $fieldMeta
     * @param bool $boolSanitize
     * @param bool $boolEncapsulateInQuotes
     * @return int|mixed|null|string
     */
    protected function ReturnFormattedData($data,$fieldMeta,$boolSanitize=false,$boolEncapsulateInQuotes=false){

        $filterType = $fieldMeta['FilterTypeNum'];
        $boolAllowsNull = in_array($fieldMeta['IS_NULLABLE'], array('YES',1,true)) ? true : false ;
        $boolIsDateOrTime = (stristr($fieldMeta['DATA_TYPE'],'date')!== FALSE || stristr($fieldMeta['DATA_TYPE'],'time')!== FALSE );
        $boolRequiresEscape = $fieldMeta['BoolQuoteWhenPopulating'];
        $boolIsNumeric = $fieldMeta['IS_NUMERIC'];

        $escapeChar = ($boolRequiresEscape && $boolEncapsulateInQuotes) ? $this::CHAR_ESCAPE_FIELD_VALUE : "";

        $fieldValue = $data;


        if($boolSanitize){
            switch($filterType){
                case $this::FILTER_TYPE_STRING:
                    $fieldValue = filter_var($fieldValue,FILTER_SANITIZE_STRING);
                    break;

                case $this::FILTER_TYPE_INT:
                    $fieldValue =  filter_var($fieldValue,FILTER_SANITIZE_NUMBER_INT);
                    break;

                case $this::FILTER_TYPE_FLOAT:
                    $fieldValue =  filter_var($fieldValue,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ;
                    break;

                case $this::FILTER_TYPE_BOOL:
                    $fieldValue =  boolval($fieldValue) ? 1 : 0 ;
                    break;
            }
        }

        $fieldValue = ($boolIsNumeric && !is_numeric($fieldValue)) ? 'null' : $fieldValue;
        $fieldValue = ($boolIsDateOrTime && $fieldValue=='') ? null : $fieldValue;
        $fieldValue = $escapeChar.$fieldValue.$escapeChar ;
        return $fieldValue;
    }



}

function removeCreationAndModificationFieldsFromArray($arrayOfFields){
    return array_diff($arrayOfFields, array('creationDateTime', 'modifiedDateTime'));
}