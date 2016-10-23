<?php
/**
 * Created by "Helping Developers With Class".
 * User: BADASSDESKTOP$
 * Timestamp: October 22, 2016, 10:09 pm
 */
require_once(DIR_ROOT . '/lib/classes/tables/Table.php');

Class child_revenue EXTENDS Table  {
    const CHAR_ESCAPE_FIELD_VALUE = "'" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "[";
    const CHAR_ESCAPE_FIELD_NAME_POST = "]";


//          COLUMN_NAME					DATA_TYPE								IS_NULLABLE		COLUMN_KEY		MAX_LENGTH		COLUMN_DEFAULT			EXTRA						IS_NUMERIC		BOUND_PARAM_TYPE
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public $childID;					//int									0				1				4																	1				i
    public $revenueID;					//int									0				1				4																	1				i
    public $std_creation;				//datetime2								1				0				8																					s
    public $std_modification;			//datetime2								1				0				8																					s

    public $allFieldNames = array('childID', 'revenueID', 'std_creation', 'std_modification');
    public $allFieldsWithoutKeys = array('std_creation', 'std_modification');

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//If you create any properties that aren't associated with a field from this table, please define them underneath this line
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


    /**
     * Returns an associative array containing metadata about the fields in the table that this class describes
     * @return array
     */
    protected function GetTableMetaAsAssocArray(){
        $record = array(
            'childID'=>						array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'1',				"MAX_LENGTH"=>4,				"IS_NUMERIC"=>1,				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0,	"BOUND_PARAM_TYPE"=>'i'),
            'revenueID'=>					array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'1',				"MAX_LENGTH"=>4,				"IS_NUMERIC"=>1,				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0,	"BOUND_PARAM_TYPE"=>'i'),
            'std_creation'=>				array(		"DATA_TYPE"=>'datetime2',				"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'0',				"MAX_LENGTH"=>8,				"IS_NUMERIC"=>0,				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),
            'std_modification'=>			array(		"DATA_TYPE"=>'datetime2',				"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'0',				"MAX_LENGTH"=>8,				"IS_NUMERIC"=>0,				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s')
        );
        return $record;
    }


    /**
     * Will attempt to load up all of this class' members based on the primary key parameter specified
     * @param childID
     */
    public function load($param_childID) {
        $pk_boundParamType = $this->GetBoundParamTypeString(array('childID'));
        $db = get_db_connection();
        $sql = 'SELECT * FROM [child_revenue] WHERE [childID] = ?';
        $rs = $db->query($sql, null, null, array($param_childID));

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
        //If user passes *, then we'll attempt to save all columns (except for the primary key) to the database
        if ($listOfFields=='*')
            $listOfFields=$this->allFieldsWithoutKeys;
        $db = get_db_connection();
        //Create an indexed array of all the values we're about to save
        $currentRecord_numeric = $this->GetArrayOfFieldValues($listOfFields, child_revenue::ARRAY_TYPE_NUMERIC, false, false, true, true);

        if (empty($this->childID)) {
            //INSERT new record when this class's primary key property is empty
            $sql = 'INSERT INTO [child_revenue]'.
                ' (['.implode('], [', $listOfFields ).'])' .
                ' VALUES ('. str_repeat ( '?,' , count($listOfFields)-1) .'?) ';
            $rs = $db->query($sql, null, null, $currentRecord_numeric);
            if ($rs) {
                $this->childID = $db->insertID();
                return true;
            } else {
                return false;
            }
        }else{
            //UPDATE existing record based on this class's primary key

            $sql = 'UPDATE [child_revenue] SET ' .
                '['.implode(']=?, [', $listOfFields ) . ']=? ' .
                '   WHERE [childID] = ?';
            $currentRecord_numeric[] = $this->childID;
            $rs = $db->query($sql, null, null, $currentRecord_numeric);
            if ($rs) {
                $this->childID =  $db->insertID();
                return true;
            } else {
                return false;
            }
        }
    }

}