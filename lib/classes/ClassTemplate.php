<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2016-01-29
 * Time: 10:41 PM
 *
 * Purpose: To template our current TableClass Declarations
 */

define('METADATA_FIELDNAME_FIELD', 'COLUMN_NAME');
define('METADATA_FIELDNAME_TYPE', 'DATA_TYPE');
define('METADATA_FIELDNAME_NULL', 'IS_NULLABLE');
define('METADATA_FIELDNAME_KEY', 'COLUMN_KEY');
define('METADATA_FIELDNAME_DEFAULT', 'COLUMN_DEFAULT');
define('METADATA_FIELDNAME_EXTRA', 'EXTRA');
define('METADATA_FIELDNAME_NUMERIC', 'IS_NUMERIC');
define('METADATA_FIELDNAME_MAX_LENGTH', 'MAX_LENGTH');
define('METADATA_FIELDNAME_BOUNDPARAMTYPE', 'BOUND_PARAM_TYPE');



Class ClassTemplate {
    private $table;
    private $columns = array();
    private $keyColumnIndexes = array();
    private $keyColumnNames = array();
    private $dbType;

    public $char_escapeNamePre = "";
    public $char_escapeNamePost = "";
    public $char_escapeValue = "'";

    private $dataTypes_numeric=array('tinyint','smallint','mediumint', 'int','bigint','float','double','decimal','numeric','money','smallmoney','real');
    private $dataTypes_boolean=array('bit');
    private $dataTypes_integer=array('tinyint','smallint','mediumint', 'int','bigint');
    private $dataTypes_float=array('float','double','decimal','real','numeric','money','smallmoney');


    /**
     * @param $param_table
     * @param array $param_columns
     * @param string $dbType
     */
    function __construct($param_table, $param_columns=[], $dbType="MySQL"){
        $this->table=$param_table;
        $this->dbType=$dbType;

        if($this->dbType=="MySQL"){
            $this->char_escapeNamePre ="`";
            $this->char_escapeNamePost ="`";
            $this->char_escapeValue = "'";
        }
        elseif($this->dbType=="MSSQL"){
            $this->char_escapeNamePre ="[";
            $this->char_escapeNamePost ="]";
            $this->char_escapeValue = "'";
        }else{
            $this->char_escapeNamePre ="";
            $this->char_escapeNamePost ="";
            $this->char_escapeValue = "";
        }

        foreach ($param_columns as $columnIndex => $column){
            $this->AddColumn($column);
        }
    }


    /**
     * @param $column
     */
    public function AddColumn($column){
        $this->columns[] = $column;

        $arraySize = count($this->columns);

        //Add the column to keyColumnIndexes when applicable
        if (array_key_exists(METADATA_FIELDNAME_KEY,$column) && (strtoupper($column[METADATA_FIELDNAME_KEY])=='PRI' || strtoupper($column[METADATA_FIELDNAME_KEY])=='1')){
            $this->keyColumnIndexes[]= ($arraySize - 1) ;
            $this->keyColumnNames[]= ($column[METADATA_FIELDNAME_FIELD]) ;
        }

        //Check to see if column is numeric
        if (array_key_exists(METADATA_FIELDNAME_TYPE,$column)){
            $this->columns[$arraySize - 1][METADATA_FIELDNAME_NUMERIC] = in_array(strtolower($column[METADATA_FIELDNAME_TYPE]),$this->dataTypes_numeric);

            //Assign the proper bound parameter type
            if(in_array(strtolower($column[METADATA_FIELDNAME_TYPE]),$this->dataTypes_boolean) || in_array(strtolower($column[METADATA_FIELDNAME_TYPE]),$this->dataTypes_integer)){
                $this->columns[$arraySize - 1][METADATA_FIELDNAME_BOUNDPARAMTYPE] = 'i';
            }elseif(in_array(strtolower($column[METADATA_FIELDNAME_TYPE]),$this->dataTypes_float)){
                $this->columns[$arraySize - 1][METADATA_FIELDNAME_BOUNDPARAMTYPE] = 'd';
            }
            else{
                $this->columns[$arraySize - 1][METADATA_FIELDNAME_BOUNDPARAMTYPE] = 's';
            }
        }



    }


    /**
     * @param $allColumns
     */
    public function SetAllColumns($allColumns){
        //$this->columns=$allColumns;
        unset($this->columns);
        foreach ($allColumns as $column){
            $this->AddColumn($column);
        }
    }




    /**
     * @return string
     */
    public function GetDeclaration_WholeClass(){

        $currentUser = getenv('USERNAME') ?: getenv('USER');
        $currentDateTime = date("F j, Y, g:i a");

        return <<<CLASS_DECLARATION
<?php
/**
 * Created by "Helping Developers With Class".
 * User: $currentUser
 * Timestamp: $currentDateTime
 */
require_once(DIR_ROOT . '/lib/classes/tables/GeneratedClass.php');

Class {$this->table} EXTENDS GeneratedClass  {
    const CHAR_ESCAPE_FIELD_VALUE = "{$this->char_escapeValue}" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "{$this->char_escapeNamePre}";
    const CHAR_ESCAPE_FIELD_NAME_POST = "{$this->char_escapeNamePost}";
    
    protected \$fields_excluded_locally = array();

{$this->GetDeclaration_Members()}

{$this->GetDeclaration_TableMetadata()}

{$this->GetDeclaration_Load()}

{$this->GetDeclaration_Save()}

}
CLASS_DECLARATION;
    }



    /**
     * @return string
     */
    public function GetDeclaration_Members(){

        $fieldsWithoutKeys = array();
        foreach($this->columns as $thisColumn){
            if(!in_array($thisColumn[METADATA_FIELDNAME_FIELD],$this->keyColumnNames)){
                $fieldsWithoutKeys[]=$thisColumn[METADATA_FIELDNAME_FIELD];
            }
        }

        $widthInTabStops = 10;

        //Template the member declaration column headers
        $output = PHP_EOL;
        $output .= $this->ColumnifyString('//          ' . METADATA_FIELDNAME_FIELD, $widthInTabStops);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_TYPE, $widthInTabStops);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_NULL, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_KEY, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_MAX_LENGTH, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_DEFAULT, 6);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_EXTRA, 7);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_NUMERIC, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_BOUNDPARAMTYPE, 4);
        $output .= PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
        $output .= PHP_EOL;

        //Member definitions (and metadata added into inline comments)
        foreach($this->columns as $index => $column) {
            $output .= $this->ColumnifyString('    public $' . $column[METADATA_FIELDNAME_FIELD] . ';' , $widthInTabStops);
            $output .= $this->ColumnifyString('//' . $column[METADATA_FIELDNAME_TYPE], $widthInTabStops);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_NULL], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_KEY], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_MAX_LENGTH], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_DEFAULT], 6);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_EXTRA], 7);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_NUMERIC], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_BOUNDPARAMTYPE], 4);
            $output .= PHP_EOL;
        }
        $output .= PHP_EOL;
        $output .= '    public $allFieldNames = array(\'' . implode('\', \'', array_column($this->columns, METADATA_FIELDNAME_FIELD)) . '\');' . PHP_EOL;
        $output .= '    public $allFieldsWithoutKeys = array(\'' . implode('\', \'', $fieldsWithoutKeys) . '\');' . PHP_EOL;
        $output .= PHP_EOL;

        //A message to alert developers who might use this class.  Any non-field related properties that they might add to this class should be added the following comment.  Doing so will allow for easy updates to this class using this utility at a later time
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;
        $output .= '//If you create any properties that aren\'t associated with a field from this table, please define them underneath this line'. PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;
        return $output;
    }



    /**
     * @return string
     */
    public function GetDeclaration_Load(){
        $_fieldName = $this->columns[$this->keyColumnIndexes[0]][METADATA_FIELDNAME_FIELD];
        $_tableName = $this->table;
        $boundParamAddition = ($this->dbType=="MySQL") ? "\$pk_boundParamType," : "";
        $declaration=
<<<LOAD_DECLARATION
    /**
    * Will attempt to load up all of this class' members based on the primary key parameter specified
    * @param $_fieldName
    */
    public function load(\$param_$_fieldName) {
        \$pk_boundParamType = \$this->GetBoundParamTypeString(array('$_fieldName'));
        \$db = get_db_connection();
        \$sql = 'SELECT * FROM {$this->char_escapeNamePre}$_tableName{$this->char_escapeNamePost} WHERE {$this->char_escapeNamePre}$_fieldName{$this->char_escapeNamePost} = ?';
        \$rs = \$db->query(\$sql, null, null, array($boundParamAddition\$param_$_fieldName));

        if(\$rs && \$rs->rowCount() > 0) {
            \$row = \$rs->fetch(CoreDB::FETCH_ASSOC);
            \$this->loadFromArray(\$row);
        }
    }
LOAD_DECLARATION;
        return $declaration;
    }



    /**
     * @return string
     */
    public function GetDeclaration_Save(){
        $_tableName = $this->table;
        $_fieldName = $this->columns[$this->keyColumnIndexes[0]][METADATA_FIELDNAME_FIELD];
        $_fieldValue = "\$this->{$_fieldName}";

        $boundParamAddition = ($this->dbType=="MySQL") ? 'array_unshift($field_values,$this->GetBoundParamTypeString($field_names));' : "";
        $boundParamAddition2 = ($this->dbType=="MySQL") ? "\$field_values[0] = \$field_values[0] . \$this->GetBoundParamTypeString(array('$_fieldName'));" : "";

        return
<<<COLUMN_IMPLOSION
    /**
     * Will attempt to save the current record
     * An INSERT will be performed if the primary key for \$this is not already populated
     * An UPDATE will be performed otherwise
     * Various options are available within the function (sanitize,quote,includeEmpties,includeNulls)
     * @param string \$listOfFields --> determines which fields are to be saved
     * @return bool
     */
    public function save(\$listOfFields = "*") {
    //If user passes *, then we'll attempt to save all columns (except for the primary key) to the database
    if (\$listOfFields=='*')
        \$listOfFields=\$this->allFieldsWithoutKeys;
        elseif(!is_array(\$listOfFields)){
            \$listOfFields = array((string)\$listOfFields);
        }
       \$db = get_db_connection();
       //Create an indexed array of all the values we're about to save
       \$nameValuePairs = \$this->GetFieldsAsAssocArray(\$listOfFields);
       \$field_values = array_values(\$nameValuePairs);
       \$field_names = array_keys(\$nameValuePairs);
       $boundParamAddition
       if (empty(\$this->$_fieldName)) {
       //INSERT new record when this class's primary key property is empty
           \$sql = 'INSERT INTO {$this->char_escapeNamePre}$_tableName{$this->char_escapeNamePost}'.
            ' ({$this->char_escapeNamePre}'.implode('{$this->char_escapeNamePost}, {$this->char_escapeNamePre}', \$field_names ).'{$this->char_escapeNamePost})' .
            ' VALUES ('. str_repeat ( '?,' , count(\$field_names)-1) .'?) ';
			\$rs = \$db->query(\$sql, null, null, \$field_values);
			if (\$rs) {
				\$this->$_fieldName = \$db->insertID();
				return true;
			} else {
				return false;
			}
        }else{
        //UPDATE existing record based on this class's primary key
        $boundParamAddition2
            \$sql = 'UPDATE {$this->char_escapeNamePre}$_tableName{$this->char_escapeNamePost} SET ' .
            '{$this->char_escapeNamePre}'.implode('{$this->char_escapeNamePost}=?, {$this->char_escapeNamePre}', \$field_names ) . '{$this->char_escapeNamePost}=? ' .
'   WHERE {$this->char_escapeNamePre}$_fieldName{$this->char_escapeNamePost} = ?';
        \$field_values[] = $_fieldValue;
        \$rs = \$db->query(\$sql, null, null, \$field_values);
        if (\$rs) {
            \$this->$_fieldName =  \$db->insertID();
            return true;
        } else {
            return false;
        }
    }
}
COLUMN_IMPLOSION;
    }



    /**
     * @param $dataType
     * @return string
     */
    private function GetFilterTypeNum($dataType){
        switch (true){
            case (in_array($dataType,$this->dataTypes_boolean)):
                return '$this::FILTER_TYPE_BOOL';
            case (in_array($dataType,$this->dataTypes_integer)):
                return '$this::FILTER_TYPE_INT';
            case (in_array($dataType,$this->dataTypes_float)):
                return '$this::FILTER_TYPE_FLOAT';
            default:
                return '$this::FILTER_TYPE_STRING';
        }
    }



    /**
     * @return string
     */
    public function GetDeclaration_TableMetadata(){
        $widthInTabStops = 8;
        $template =
            '    /**' . PHP_EOL .
            '* Returns an associative array containing metadata about the fields in the table that this class describes' . PHP_EOL .
            '* @return array' . PHP_EOL .
            '*/' . PHP_EOL .
            '    protected function GetTableMetaAsAssocArray(){' . PHP_EOL .
            '        $record = array(' .PHP_EOL ;
        $countFields = count($this->columns);
        foreach($this->columns as $fieldNum => $field){
            $boolQuoteWhenPopulating = !in_array($field[METADATA_FIELDNAME_TYPE], $this->dataTypes_numeric) ? 1 : 0;
            $filterTypeNum = $this->GetFilterTypeNum($field[METADATA_FIELDNAME_TYPE]);
            $max_length = (float)$field[METADATA_FIELDNAME_MAX_LENGTH];
            $is_numeric = (int)$field[METADATA_FIELDNAME_NUMERIC];
            $nullField = $field[METADATA_FIELDNAME_NULL];
            $is_nullable = ($nullField == "YES" || $nullField == '1' )? 1 : 0;

            $comma = $fieldNum < $countFields - 1 ? ',' : '' ;
            $thisField = $this->ColumnifyString("'{$field[METADATA_FIELDNAME_FIELD]}'=>" , $widthInTabStops)  ;
            $thisValue = $this->ColumnifyString('array(' , 3);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_TYPE.'"=>\''. addslashes("{$field[METADATA_FIELDNAME_TYPE]}") . "'," , $widthInTabStops + 2);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_NULL.'"=>'."{$is_nullable}," , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_KEY.'"=>\''."{$field[METADATA_FIELDNAME_KEY]}'," , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_MAX_LENGTH.'"=>'."{$max_length}," , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_NUMERIC.'"=>'."{$is_numeric}," , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"FilterTypeNum"=>' . $filterTypeNum . ',' , $widthInTabStops + 4);
            $thisValue .= $this->ColumnifyString('"BoolQuoteWhenPopulating"=>' . $boolQuoteWhenPopulating . ',' , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_BOUNDPARAMTYPE.'"=>\''."{$field[METADATA_FIELDNAME_BOUNDPARAMTYPE]}')" . $comma  , $widthInTabStops)  . PHP_EOL;
            $template .= '			' . $thisField . $thisValue ;
        }
        $template .=
            '        );' . PHP_EOL .
            '        return $record;' . PHP_EOL .
            '    }' .PHP_EOL;

        return $template;
    }




    /**
     * @param $myString
     * @param $columnWidthInTabStops
     * @return string
     */
    private static function ColumnifyString($myString, $columnWidthInTabStops){
        $numRepetitionsGuess =$columnWidthInTabStops - (floor(strlen($myString)/4)) ;
        $numRepetitions= $numRepetitionsGuess >0 ? $numRepetitionsGuess : 0 ;
        $myTrailingTabSpace = str_repeat("\t", $numRepetitions );
        return $myString . $myTrailingTabSpace;
    }

}