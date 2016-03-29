<?php
/**
 * Created by "Helping Developers With Class".
 * User: WEBDEV$
 * Timestamp: March 26, 2016, 12:51 pm
 */
require_once('Table.php');

Class Activity EXTENDS Table  {

    const FILTER_TYPE_NONE = 0;
    const FILTER_TYPE_BOOL = 1;
    const FILTER_TYPE_INT = 2;
    const FILTER_TYPE_FLOAT = 3;
    const FILTER_TYPE_STRING = 4;

    const CHAR_ESCAPE_FIELD_VALUE = "'" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "[";
    const CHAR_ESCAPE_FIELD_NAME_POST = "]";


//          COLUMN_NAME					DATA_TYPE								IS_NULLABLE		COLUMN_KEY		COLUMN_DEFAULT	EXTRA
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public $activity_p;					//int									0				1
    public $activityName;				//nvarchar								1				0
    public $activityNumber;				//nvarchar								1				0
    public $status;						//nvarchar								1				0
    public $projectID_f;				//int									1				0
    public $element_num;				//int									1				0
    public $plan_start_date;			//date									1				0
    public $plannedhours;				//float									1				0
    public $phase_name;					//nvarchar								1				0
    public $zkRecord_No;				//nvarchar								1				0
    public $za_endDate_display;			//date									1				0
    public $type;						//nvarchar								1				0
    public $rate;						//float									1				0
    public $percent_complete;			//float									1				0
    public $billableOrNonBillable;		//nvarchar								1				0
    public $planned_hours_remaining;	//float									1				0
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//If you create any properties that aren't associated with a field from this table, please define them underneath this line
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


    private function GetTableMetaAsAssocArray(){
        $record = array(
            'activity_p'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>'0',				"COLUMN_KEY"=>'1',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0),
            'activityName'=>				array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'activityNumber'=>				array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'status'=>						array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'projectID_f'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0),
            'element_num'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0),
            'plan_start_date'=>				array(		"DATA_TYPE"=>'date',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'plannedhours'=>				array(		"DATA_TYPE"=>'float',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_FLOAT,		"BoolQuoteWhenPopulating"=>0),
            'phase_name'=>					array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'zkRecord_No'=>					array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'za_endDate_display'=>			array(		"DATA_TYPE"=>'date',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'type'=>						array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'rate'=>						array(		"DATA_TYPE"=>'float',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_FLOAT,		"BoolQuoteWhenPopulating"=>0),
            'percent_complete'=>			array(		"DATA_TYPE"=>'float',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_FLOAT,		"BoolQuoteWhenPopulating"=>0),
            'billableOrNonBillable'=>		array(		"DATA_TYPE"=>'nvarchar',				"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1),
            'planned_hours_remaining'=>		array(		"DATA_TYPE"=>'float',					"IS_NULLABLE"=>'1',				"COLUMN_KEY"=>'0',				"FilterTypeNum"=>$this::FILTER_TYPE_FLOAT,		"BoolQuoteWhenPopulating"=>0)
        );
        return $record;
    }


    public function load($param_activity_p) {
        $db = get_db_connection();
        $sql = 'SELECT * FROM `Activity` WHERE `activity_p` = ?';
        $rs = $db->query($sql, null, null, array($param_activity_p));

        if($rs && $rs->rowCount() > 0) {
            $row = $rs->fetch(CoreDB::FETCH_ASSOC);
            $this->loadFromArray($row);
        }
    }

    public function save($listOfFields = "*") {
        if ($listOfFields=='*')
            $listOfFields=array('activity_p', 'activityName', 'activityNumber', 'status', 'projectID_f', 'element_num', 'plan_start_date', 'plannedhours', 'phase_name', 'zkRecord_No', 'za_endDate_display', 'type', 'rate', 'percent_complete', 'billableOrNonBillable', 'planned_hours_remaining');
        $db = get_db_connection();
        $currentRecord = $this->GetAssocArrayFromListOfFields($listOfFields);
        if (empty($this->activity_p)) {
            $sql = 'INSERT INTO `Activity`'.
                ' (`'.implode('`, `', array_keys($currentRecord)).'`)' .
                ' VALUES ('.implode(', ', $currentRecord).') ';
            $rs = $db->query($sql, null, null, array_keys($currentRecord));
            if ($rs) {
                $this->activity_p = $db->insertID();
                return true;
            } else {
                return false;
            }
        }else{
            $sql = 'UPDATE `Activity` SET ' .
                '`'.implode('`, `', array_keys($currentRecord)) . '` = ?' .
                '   WHERE `activity_p` = ?';
            $rs = $db->query($sql, null, null, $currentRecord);
            if ($rs) {
                $this->activity_p =  $db->insertID();
                return true;
            } else {
                return false;
            }
        }
    }

    public function GetAssocArrayFromListOfFields($listOfFields = "*", $excludeEmpties = false)
    {
        if ($listOfFields=='*')
            $listOfFields=array('activity_p', 'activityName', 'activityNumber', 'status', 'projectID_f', 'element_num', 'plan_start_date', 'plannedhours', 'phase_name', 'zkRecord_No', 'za_endDate_display', 'type', 'rate', 'percent_complete', 'billableOrNonBillable', 'planned_hours_remaining');
        $result = array();
        foreach ($listOfFields as $fieldName) {
            if (property_exists($this, $fieldName)) {
                $filteredResult = $this->FilterAndEscapeField($fieldName);
                $boolIsAnEmpty = !isset($filteredResult) || $filteredResult == '' || $filteredResult == $this::CHAR_ESCAPE_FIELD_VALUE . $this::CHAR_ESCAPE_FIELD_VALUE;
                if (!$boolIsAnEmpty || !$excludeEmpties)
                    $result[$fieldName] = $filteredResult;
            }
        }
        return $result;
    }

    public function FilterAndEscapeField($fieldName){
        if(property_exists($this,$fieldName)){
            $tableMeta = $this->GetTableMetaAsAssocArray();

            $filterType = $tableMeta[$fieldName]['FilterTypeNum'];
            $boolAllowsNull = $tableMeta[$fieldName]['IS_NULLABLE'] == 'YES' ? true : false ;
            $boolRequiresEscape = $tableMeta[$fieldName]['BoolQuoteWhenPopulating'];

            $escapeChar = $boolRequiresEscape ? $this::CHAR_ESCAPE_FIELD_VALUE : "";

            $fieldValue = $this->$fieldName;
            $returnValue = '';

            switch($filterType){
                case $this::FILTER_TYPE_STRING:
                    $returnValue = filter_var($fieldValue,FILTER_SANITIZE_STRING);
                    break;

                case $this::FILTER_TYPE_INT:
                    $returnValue =  filter_var($fieldValue,FILTER_SANITIZE_NUMBER_INT);
                    break;

                case $this::FILTER_TYPE_FLOAT:
                    $returnValue =  filter_var($fieldValue,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ;
                    break;

                case $this::FILTER_TYPE_BOOL:
                    $returnValue =  boolval($fieldValue) ? 1 : 0 ;
                    break;
            }

            $returnValue = $escapeChar.$returnValue.$escapeChar ;
            if ( ($returnValue=='' || $returnValue == $escapeChar.$escapeChar) && $boolAllowsNull) {
                return $escapeChar . NULL . $escapeChar;
            }
            elseif ( ($returnValue=='' || $returnValue == $escapeChar.$escapeChar) && $boolAllowsNull){
                return false;
            }
            else return $returnValue;
        }
    }

}