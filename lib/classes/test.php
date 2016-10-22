<?php
/**
 * Created by "Helping Developers With Class".
 * User: BADASSDESKTOP$
 * Timestamp: October 21, 2016, 10:12 pm
 */
require_once(DIR_ROOT . '/lib/classes/tables/Table.php');

Class revenue EXTENDS Table  {

    const FILTER_TYPE_NONE = 0;
    const FILTER_TYPE_BOOL = 1;
    const FILTER_TYPE_INT = 2;
    const FILTER_TYPE_FLOAT = 3;
    const FILTER_TYPE_STRING = 4;

    const ARRAY_TYPE_NUMERIC = 1;
    const ARRAY_TYPE_ASSOC = 2;
    const ARRAY_TYPE_BOTH = 3;

    const CHAR_ESCAPE_FIELD_VALUE = "'" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "[";
    const CHAR_ESCAPE_FIELD_NAME_POST = "]";


//          COLUMN_NAME					DATA_TYPE								IS_NULLABLE		COLUMN_KEY		COLUMN_DEFAULT	EXTRA			IS_NUMERIC		BOUND_PARAM_TYPE
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public $revenueID;					//int									0				1												1				i
    public $familyID;					//int									1				0												1				i
    public $std_creation;				//datetime2								1				0																s
    public $std_modification;			//datetime2								1				0																s
    public $dateReceived;				//date									1				0																s
    public $amount;						//decimal								1				0												1				d
    public $description;				//nvarchar								1				0																s

    public $allFieldNames = array('revenueID', 'familyID', 'std_creation', 'std_modification', 'dateReceived', 'amount', 'description');
    public $allFieldsWithoutKeys = array('familyID', 'std_creation', 'std_modification', 'dateReceived', 'amount', 'description');

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//If you create any properties that aren't associated with a field from this table, please define them underneath this line
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


    /**
     * Returns an associative array containing metadata about the fields in the table that this class describes
     * @return array
     */
    private function GetTableMetaAsAssocArray(){
        $record = array(
            'revenueID'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>'0',				"COLUMN_KEY"=>'1',				"IS_NUMERIC"=>'1',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0,	"BOUND_PARAM_TYPE"=>'i'),
            'familyID'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'1',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0,	"BOUND_PARAM_TYPE"=>'i'),
            'std_creation'=>				array(		"DATA_TYPE"=>'datetime2',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),
            'std_modification'=>			array(		"DATA_TYPE"=>'datetime2',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),
            'dateReceived'=>				array(		"DATA_TYPE"=>'date',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),
            'amount'=>						array(		"DATA_TYPE"=>'decimal',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'1',				"FilterTypeNum"=>$this::FILTER_TYPE_FLOAT,		"BoolQuoteWhenPopulating"=>0,	"BOUND_PARAM_TYPE"=>'d'),
            'description'=>					array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s')
        );
        return $record;
    }


    /**
     * Will attempt to load up all of this class' members based on the primary key parameter specified
     * @param revenueID
     */
    public function load($param_revenueID) {
        $pk_boundParamType = $this->GetBoundParamTypeString(array('revenueID'));
        $db = get_db_connection();
        $sql = 'SELECT * FROM [revenue] WHERE [revenueID] = ?';
        $rs = $db->query($sql, null, null, array($param_revenueID));

        if($rs && $rs->rowCount() > 0) {
            $row = $rs->fetch(CoreDB::FETCH_ASSOC);
            $this->loadFromArray($row);
        }
    }

    /**
     * Will attempt to save the current record
     * An INSERT will be performed if the primary key for $this is not already populated
     * An UPDATE will be performed otherwise
     * Various options are available within the function (sanitize,quote,includeEmpties,includeNulls)
     * @param string $listOfFields --> determines which fields are to be saved
     * @return bool
     */
    public function save($listOfFields = "*") {
        if ($listOfFields=='*')
            $listOfFields=$this->allFieldsWithoutKeys;
        $db = get_db_connection();
        $currentRecord_numeric = $this->GetArrayOfFieldValues($listOfFields, revenue::ARRAY_TYPE_NUMERIC, false, false, true, true);

        if (empty($this->revenueID)) {
            $sql = 'INSERT INTO [revenue]'.
                ' (['.implode('], [', $listOfFields ).'])' .
                ' VALUES ('. str_repeat ( '?,' , count($listOfFields)-1) .'?) ';
            $rs = $db->query($sql, null, null, $currentRecord_numeric);
            if ($rs) {
                $this->revenueID = $db->insertID();
                return true;
            } else {
                return false;
            }
        }else{

            $sql = 'UPDATE [revenue] SET ' .
                '['.implode(']=?, [', $listOfFields ) . ']=? ' .
                '   WHERE [revenueID] = ?';
            $currentRecord_numeric[] = $this->revenueID;
            $rs = $db->query($sql, null, null, $currentRecord_numeric);
            if ($rs) {
                $this->revenueID =  $db->insertID();
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * This function is primarily only invoked privately
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
    public function GetArrayOfFieldValues($listOfFields='*', $arrayType=revenue::ARRAY_TYPE_ASSOC, $boolUseSanitizeFilters=false, $boolEncapsulateInQuotes=false, $boolIncludeEmpties=true, $boolIncludeNulls=true){
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
     * This function is to be used when a MySQL database is the source of data
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
     * This function is used for sanitizing data
     * It probably won't get used much since parameterized queries are now in effect
     * It could probably use some more work if it is going to be used too
     * @param $data
     * @param $fieldMeta
     * @param bool $boolSanitize
     * @param bool $boolEncapsulateInQuotes
     * @return int|mixed|null|string
     */
    private function ReturnFormattedData($data,$fieldMeta,$boolSanitize=false,$boolEncapsulateInQuotes=false){

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