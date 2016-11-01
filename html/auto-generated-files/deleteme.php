<?php
/**
 * Created by "Helping Developers With Class".
 * User: BADASSDESKTOP$
 * Timestamp: October 26, 2016, 1:20 pm
 */
require_once(DIR_ROOT . '/lib/classes/tables/GeneratedClass.php');

Class test EXTENDS GeneratedClass  {
    const CHAR_ESCAPE_FIELD_VALUE = "'" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "`";
    const CHAR_ESCAPE_FIELD_NAME_POST = "`";
    
    protected $fields_excluded_locally = array();


//          COLUMN_NAME					DATA_TYPE								IS_NULLABLE		COLUMN_KEY		MAX_LENGTH		COLUMN_DEFAULT			EXTRA						IS_NUMERIC		BOUND_PARAM_TYPE
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public $test_pk;					//int									NO				PRI				10.0									auto_increment				1				i				
    public $creationDateTime;			//timestamp								YES												CURRENT_TIMESTAMP													s				
    public $modifiedDateTime;			//timestamp								YES												CURRENT_TIMESTAMP		on update CURRENT_TIMESTAMP					s				
    public $testName;					//varchar								YES								100																					s				
    public $cannotBeNull;				//varchar								NO								45																					s				

    public $allFieldNames = array('test_pk', 'creationDateTime', 'modifiedDateTime', 'testName', 'cannotBeNull');
    public $allFieldsWithoutKeys = array('creationDateTime', 'modifiedDateTime', 'testName', 'cannotBeNull');

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//If you create any properties that aren't associated with a field from this table, please define them underneath this line
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


    /**
* Returns an associative array containing metadata about the fields in the table that this class describes
* @return array
*/
    protected function GetTableMetaAsAssocArray(){
        $record = array(
			'test_pk'=>						array(		"DATA_TYPE"=>'int',						"IS_NULLABLE"=>0,				"COLUMN_KEY"=>'PRI',			"MAX_LENGTH"=>10,				"IS_NUMERIC"=>1,				"FilterTypeNum"=>$this::FILTER_TYPE_INT,		"BoolQuoteWhenPopulating"=>0,	"BOUND_PARAM_TYPE"=>'i'),		
			'creationDateTime'=>			array(		"DATA_TYPE"=>'timestamp',				"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'',				"MAX_LENGTH"=>0,				"IS_NUMERIC"=>0,				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),		
			'modifiedDateTime'=>			array(		"DATA_TYPE"=>'timestamp',				"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'',				"MAX_LENGTH"=>0,				"IS_NUMERIC"=>0,				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),		
			'testName'=>					array(		"DATA_TYPE"=>'varchar',					"IS_NULLABLE"=>1,				"COLUMN_KEY"=>'',				"MAX_LENGTH"=>100,				"IS_NUMERIC"=>0,				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s'),		
			'cannotBeNull'=>				array(		"DATA_TYPE"=>'varchar',					"IS_NULLABLE"=>0,				"COLUMN_KEY"=>'',				"MAX_LENGTH"=>45,				"IS_NUMERIC"=>0,				"FilterTypeNum"=>$this::FILTER_TYPE_STRING,		"BoolQuoteWhenPopulating"=>1,	"BOUND_PARAM_TYPE"=>'s')		
        );
        return $record;
    }


    /**
    * Will attempt to load up all of this class' members based on the primary key parameter specified
    * @param test_pk
    */
    public function load($param_test_pk) {
        $pk_boundParamType = $this->GetBoundParamTypeString(array('test_pk'));
        $db = get_db_connection();
        $sql = 'SELECT * FROM `test` WHERE `test_pk` = ?';
        $rs = $db->query($sql, null, null, array($pk_boundParamType,$param_test_pk));

        if($rs && $rs->rowCount() > 0) {
            $row = $rs->fetch(CoreDB::FETCH_ASSOC);
            $this->loadFromArray($row);
        }
    }

    /**
     * Will attempt to save the current record
     * An INSERT will be performed if the primary key for $this is not already populated
     * An UPDATE will be performed otherwise
     * Various options will be available within the function --> still under construction(sanitize,quote,includeEmpties,includeNulls)
     * @param string/array $listOfFields --> determines which fields are to be saved (single fieldname string or indexed array of fieldnames)
     * @return bool
     */
    public function save($listOfFields = "*") {
    //If user passes *, then we'll attempt to save all columns (except for the primary key) to the database
    if ($listOfFields=='*')
        $listOfFields=$this->allFieldsWithoutKeys;
        elseif(!is_array($listOfFields)){
            $listOfFields = array((string)$listOfFields);
        }
       $db = get_db_connection();
       //Create an assoc array of all the values we're about to save
       $nameValuePairs = $this->GetFieldsAsAssocArray($listOfFields);
       $field_values = array_values($nameValuePairs);
       $field_names = array_keys($nameValuePairs);
       array_unshift($field_values,$this->GetBoundParamTypeString($field_names));
       if (empty($this->test_pk)) {
       //INSERT new record when this class's primary key property is empty
           $sql = 'INSERT INTO `test`'.
            ' (`'.implode('`, `', $field_names ).'`)' .
            ' VALUES ('. str_repeat ( '?,' , count($field_names)-1) .'?) ';
			$rs = $db->query($sql, null, null, $field_values);
			if ($rs) {
				$this->test_pk = $db->insertID();
				return true;
			} else {
				return false;
			}
        }else{
        //UPDATE existing record based on this class's primary key
        $field_values[0] = $field_values[0] . $this->GetBoundParamTypeString(array('test_pk'));
            $sql = 'UPDATE `test` SET ' .
            '`'.implode('`=?, `', $field_names ) . '`=? ' .
'   WHERE `test_pk` = ?';
        $field_values[] = $this->test_pk;
        $rs = $db->query($sql, null, null, $field_values);
        if ($rs) {
            $this->test_pk =  $db->insertID();
            return true;
        } else {
            return false;
        }
    }
}

}