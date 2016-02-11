<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2016-01-29
 * Time: 10:41 PM
 *
 * Purpose: To template our current TableClass Declarations
 */


//SELECT Distinct TABLE_NAME FROM information_schema.TABLES

//ToDo: Create in 'Table.php or generated file' => Function that will return a list of non-null fieldNames
define('METADATA_FIELDNAME_FIELD', 'COLUMN_NAME');
define('METADATA_FIELDNAME_TYPE', 'DATA_TYPE');
define('METADATA_FIELDNAME_NULL', 'IS_NULLABLE');
define('METADATA_FIELDNAME_KEY', 'COLUMN_KEY');
define('METADATA_FIELDNAME_DEFAULT', 'COLUMN_DEFAULT');
define('METADATA_FIELDNAME_EXTRA', 'EXTRA');

define('CHAR_ESCAPE_FIELD_NAME' , '`');
define('CHAR_ESCAPE_FIELD_VALUE' , '\'');


Class ClassTemplate {
    private $table;
    private $columns = array();
    private $keyColumnIndexes = array();

    function __construct($param_table, $param_columns=[]){
        $this->table=$param_table;
        foreach ($param_columns as $columnIndex => $column){
            $this->AddColumn($column);
        }
    }

    public function AddColumn($column){
        $this->columns[] = $column;

        $arraySize = count($this->columns);

        //Add the column to keyColumnIndexes when applicable
        if (array_key_exists(METADATA_FIELDNAME_KEY,$column) && strtoupper($column[METADATA_FIELDNAME_KEY])=='PRI'){
            $this->keyColumnIndexes[]= ($arraySize - 1) ;
        }
    }

    public function SetAllColumns($allColumns){
        $this->columns=$allColumns;
    }

    public function GetDeclaration_WholeClass(){
        return <<<CLASS_DECLARATION
<?php
require_once('Table.php');


Class {$this->table} EXTENDS Table  {

    const FILTER_TYPE_NONE = 0;
    const FILTER_TYPE_BOOL = 1;
    const FILTER_TYPE_INT = 2;
    const FILTER_TYPE_FLOAT = 3;
    const FILTER_TYPE_STRING = 4;

    const CHAR_ESCAPE_FIELD_VALUE = "'" ;
    const CHAR_ESCAPE_FIELD_NAME = "`";

{$this->GetDeclaration_Members()}


{$this->GetDeclaration_TableMetadata()}

{$this->GetDeclaration_Load()}

{$this->GetDeclaration_Save()}

{$this->GetDeclaration_AssocArray()}

{$this->GetDeclaration_FilterAndEscape()}

}
CLASS_DECLARATION;
    }

    public function GetDeclaration_Members(){
        $widthInTabStops = 10;

        //Template the member declaration column headers
        $output = PHP_EOL;
        $output .= $this->ColumnifyString('//          ' . METADATA_FIELDNAME_FIELD, $widthInTabStops);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_TYPE, $widthInTabStops);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_NULL, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_KEY, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_DEFAULT, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_EXTRA, 4);
        $output .= PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
        $output .= PHP_EOL;

        //Member definitions (and metadata added into inline comments)
        foreach($this->columns as $index => $column) {
            $output .= $this->ColumnifyString('    public $' . $column[METADATA_FIELDNAME_FIELD] . ';' , $widthInTabStops);
            $output .= $this->ColumnifyString('//' . $column[METADATA_FIELDNAME_TYPE], $widthInTabStops);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_NULL], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_KEY], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_DEFAULT], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_EXTRA], 4);
            $output .= PHP_EOL;
        }

        //A message to alert developers who might use this class.  Any non-field related properties that they might add to this class should be added the following comment.  Doing so will allow for easy updates to this class using this utility at a later time
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;
        $output .= '//If you create any properties that aren\'t associated with a field from this table, please define them underneath this line'. PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;
        return $output;
    }

    public function GetDeclaration_Load(){
        $_escapeName = CHAR_ESCAPE_FIELD_NAME;
        $_fieldName = $this->columns[$this->keyColumnIndexes[0]][METADATA_FIELDNAME_FIELD];
        $_tableName = $this->table;

        $declaration=
<<<LOAD_DECLARATION
    public function load(\$param_$_fieldName) {
        \$db = get_db_connection();
        \$sql = 'SELECT * FROM $_escapeName$_tableName$_escapeName WHERE $_escapeName$_fieldName$_escapeName = ?';
        \$rs = \$db->query(\$sql, null, null, array(\$param_$_fieldName));

        if(\$rs && \$rs->rowCount() > 0) {
            \$row = \$rs->fetch(CoreDB::FETCH_ASSOC);
            \$this->loadFromArray(\$row);
        }
    }
LOAD_DECLARATION;
        return $declaration;
    }

    public function GetDeclaration_Save(){
        $_escapeName = CHAR_ESCAPE_FIELD_NAME;
        $_tableName = $this->table;
        return
<<<COLUMN_IMPLOSION
    public function save() {
       \$db = get_db_connection();
       \$currentRecord = \$this->GetAssocArrayFromListOfFields();
       if (empty(\$this->Host)) {
           \$sql = 'INSERT INTO $_escapeName$_tableName$_escapeName'.
            ' ($_escapeName'.implode('$_escapeName, $_escapeName', array_keys(\$currentRecord)).'$_escapeName)' .
            ' VALUES ('.implode(', ', \$currentRecord).') ';
			\$rs = \$db->query(\$sql, null, null, array_keys(\$currentRecord));
			if (\$rs) {
				\$this->Host = \$db->insertID();
				get_msg_system()->addMessage('user' . \$this->Host. ' Saved Successfully.', Msg::GOOD);
				return true;
			} else {
				get_msg_system()->addMessage('user' . \$this->Host . ' Save Failed. ' . \$db->errorMsg(), Msg::ERROR);
				return false;
			}
        }else{
            \$sql = 'UPDATE $_escapeName$_tableName$_escapeName SET ' .
            '$_escapeName'.implode('$_escapeName, $_escapeName', array_keys(\$currentRecord)) . '$_escapeName = ?' .
'   WHERE [Host] = ?';
        \$rs = \$db->query(\$sql, null, null, \$currentRecord);
        if (\$rs) {
            \$this->Host =  \$db->insertID();
            get_msg_system()->addMessage('user ' . \$this->Host . ' Updated Successfully.', Msg::GOOD);
            return true;
        } else {
            get_msg_system()->addMessage('user ' . \$this->Host . ' Update Failed. ' . \$db->errorMsg(), Msg::ERROR);
            return false;
        }
    }
}
COLUMN_IMPLOSION;
    }

    public function GetDeclaration_TableMetadata(){
        $widthInTabStops = 10;
        $template =
            '    private function GetTableMetaAsAssocArray(){' . PHP_EOL .
            '        $record = array(' .PHP_EOL ;
        $countFields = count($this->columns);
        foreach($this->columns as $fieldNum => $field){
            $comma = $fieldNum < $countFields - 1 ? ',' : '' ;
            $thisField = $this->ColumnifyString("'{$field[METADATA_FIELDNAME_FIELD]}'=>" , $widthInTabStops)  ;
            $thisValue = $this->ColumnifyString('array(' , 3);
            $thisValue .= $this->ColumnifyString('"Type"=>\''. addslashes("{$field[METADATA_FIELDNAME_TYPE]}") . "'," , $widthInTabStops + 5);
            $thisValue .= $this->ColumnifyString('"Null"=>\''."{$field[METADATA_FIELDNAME_NULL]}'," , 4);
            $thisValue .= $this->ColumnifyString('"Key"=>\''."{$field[METADATA_FIELDNAME_KEY]}'," , 4);
            $thisValue .= $this->ColumnifyString('"FilterTypeNum"=>1,' , 6);
            $thisValue .= $this->ColumnifyString('"BoolEscapeSQLFieldName"=>1)' . $comma , 4)  . PHP_EOL;
            $template .= '			' . $thisField . $thisValue ;
        }
        $template .=
            '        );' . PHP_EOL .
            '        return $record;' . PHP_EOL .
            '    }' .PHP_EOL;

        return $template;
    }

    public function GetDeclaration_AssocArray(){
        $fieldArray = 'array(\'' . implode('\', \'', array_column($this->columns, METADATA_FIELDNAME_FIELD)) . '\')';

        return
<<<ASSOC_ARRAY
    public function GetAssocArrayFromListOfFields(\$listOfFields = "*", \$excludeEmpties = false)
    {
    if (\$listOfFields=='*')
        \$listOfFields=$fieldArray;
    \$result = array();
    foreach (\$listOfFields as \$fieldName) {
        if (property_exists(\$this, \$fieldName)) {
            \$filteredResult = \$this->FilterAndEscapeField(\$fieldName);
            \$boolIsAnEmpty = !isset(\$filteredResult) || \$filteredResult == '' || \$filteredResult == \$escapeChar . \$escapeChar;
            if (!\$boolIsAnEmpty || !\$excludeEmpties)
                \$result[\$fieldName] = \$filteredResult;
            }
        }
        return \$result;
    }
ASSOC_ARRAY;
    }

    public function GetDeclaration_FilterAndEscape(){
        $metaFieldName = METADATA_FIELDNAME_FIELD;
        return <<<FILTER_FUNCTION
    public function FilterAndEscapeField(\$fieldName){
        if(property_exists(\$this,\$fieldName)){
            \$tableMeta = \$this->GetTableMetaAsAssocArray();

            \$filterType = \$tableMeta[\$fieldName]['FilterTypeNum'];
            \$boolAllowsNull = \$tableMeta[\$fieldName][$metaFieldName] == 'YES' ? true : false ;
            \$boolRequiresEscape = \$tableMeta[\$fieldName]['BoolEscapeSQLFieldName'];

            \$escapeChar = \$boolRequiresEscape ? \$this::CHAR_ESCAPE_FIELD_VALUE : "";

            \$fieldValue = \$this->\$fieldName;
            \$returnValue = '';

            switch(\$filterType){
                case \$this::FILTER_TYPE_STRING:
                    \$returnValue = filter_var(\$fieldValue,FILTER_SANITIZE_STRING);
                    break;

                case \$this::FILTER_TYPE_INT:
                    \$returnValue =  filter_var(\$fieldValue,FILTER_SANITIZE_NUMBER_INT);
                    break;

                case \$this::FILTER_TYPE_FLOAT:
                    \$returnValue =  filter_var(\$fieldValue,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ;
                    break;

                case \$this::FILTER_TYPE_BOOL:
                    \$returnValue =  boolval(\$fieldValue) ? 1 : 0 ;
                    break;
            }

            \$returnValue = \$escapeChar.\$returnValue.\$escapeChar ;
            if ( (\$returnValue=='' || \$returnValue == \$escapeChar.\$escapeChar) && \$boolAllowsNull) {
                return \$escapeChar . NULL . \$escapeChar;
            }
            elseif ( (\$returnValue=='' || \$returnValue == \$escapeChar.\$escapeChar) && \$boolAllowsNull){
                return false;
            }
            else return \$returnValue;
        }
    }
FILTER_FUNCTION;

    }


    private static function ColumnifyString($myString, $columnWidthInTabStops){
        $numRepetitionsGuess =$columnWidthInTabStops - (floor(strlen($myString)/4)) ;
        $numRepetitions= $numRepetitionsGuess >0 ? $numRepetitionsGuess : 0 ;
        $myTrailingTabSpace = str_repeat("\t", $numRepetitions );
        return $myString . $myTrailingTabSpace;
    }

}