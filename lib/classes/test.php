<?php
/**
 * Created by "Helping Developers With Class".
 * User: WEBDEV$
 * Timestamp: March 31, 2016, 8:39 am
 */
require_once(DIR_ROOT . '/lib/classes/tables/Table.php');

Class People EXTENDS Table  {

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


//          COLUMN_NAME					DATA_TYPE								IS_NULLABLE		COLUMN_KEY		COLUMN_DEFAULT	EXTRA			IS_NUMERIC
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public $person_p;					//int									0				1												1
    public $std_dateTime2_create;		//datetime2								0				0
    public $std_dateTime2_modify;		//datetime2								1				0
    public $nameFirst;					//varchar								1				0
    public $nameLast;					//varchar								1				0
    public $boolIsCustomer;				//bit									1				0
    public $age;						//tinyint								1				0												1

    public $allFieldNames = array('person_p', 'std_dateTime2_create', 'std_dateTime2_modify', 'nameFirst', 'nameLast', 'boolIsCustomer', 'age');

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//If you create any properties that aren't associated with a field from this table, please define them underneath this line
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


    private function GetTableMetaAsAssocArray(){
        $record = array(
            'person_p'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>'0',				"COLUMN_KEY"=>'1',				"IS_NUMERIC"=>'1',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0),
            'std_dateTime2_create'=>		array(		"DATA_TYPE"=>'datetime2',				"IS_NULLABLE"=>'0',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'std_dateTime2_modify'=>		array(		"DATA_TYPE"=>'datetime2',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'nameFirst'=>					array(		"DATA_TYPE"=>'varchar',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'nameLast'=>					array(		"DATA_TYPE"=>'varchar',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'boolIsCustomer'=>				array(		"DATA_TYPE"=>'bit',						"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'',				"FilterTypeNum"=>$this::FILTER_TYPE_BOOL,		"BoolQuoteWhenPopulating"=>1),
            'age'=>							array(		"DATA_TYPE"=>'tinyint',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"IS_NUMERIC"=>'1',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0)
        );
        return $record;
    }


    public function load($param_person_p) {
        $db = get_db_connection();
        $sql = 'SELECT * FROM [People] WHERE [person_p] = ?';
        $rs = $db->query($sql, null, null, array($param_person_p));

        if($rs && $rs->rowCount() > 0) {
            $row = $rs->fetch(CoreDB::FETCH_ASSOC);
            $this->loadFromArray($row);
        }
    }

    public function save($listOfFields = "*") {
        if ($listOfFields=='*')
            $listOfFields=$this->allFieldNames;
        $db = get_db_connection();
        //$currentRecord_numeric = $this->GetNumericArrayFromListOfFields($listOfFields);
        $currentRecord_numeric = $this->GetArrayOfFieldValues($listOfFields, $this::ARRAY_TYPE_NUMERIC, false, false, true, true);
        if (empty($this->person_p)) {
            $sql = 'INSERT INTO [People]'.
                ' (['.implode('], [', $listOfFields ).'])' .
                ' VALUES ('. str_repeat ( '?,' , count($listOfFields)-1) .'?) ';
            $rs = $db->query($sql, null, null, $currentRecord_numeric);
            if ($rs) {
                $this->person_p = $db->insertID();
                return true;
            } else {
                return false;
            }
        }else{
            $sql = 'UPDATE [People] SET ' .
                '['.implode(']=?, [', $listOfFields ) . ']=? ' .
                '   WHERE [person_p] = ?';
            $currentRecord_numeric[] = $this->person_p;
            $rs = $db->query($sql, null, null, $currentRecord_numeric);
            if ($rs) {
                $this->person_p =  $db->insertID();
                return true;
            } else {
                return false;
            }
        }
    }

    public function GetArrayOfFieldValues($listOfFields='*', $arrayType=People::ARRAY_TYPE_ASSOC, $boolUseSanitizeFilters=false, $boolEncapsulateInQuotes=false, $boolIncludeEmpties=true, $boolIncludeNulls=true){
        if ($listOfFields=='*')
            $listOfFields=$this->allFieldNames;
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

    private function ReturnFormattedData($data,$fieldMeta,$boolSanitize=false,$boolEncapsulateInQuotes=false){

        $filterType = $fieldMeta['FilterTypeNum'];
        $boolAllowsNull = in_array($fieldMeta['IS_NULLABLE'], array('YES',1,true)) ? true : false ;
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

        $returnValue = ($boolIsNumeric && !is_numeric($fieldValue)) ? null : $fieldValue;
        $returnValue = $escapeChar.$fieldValue.$escapeChar ;
        return $fieldValue;
    }

}