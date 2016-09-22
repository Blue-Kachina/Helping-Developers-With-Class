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
require_once(DIR_ROOT . '/lib/classes/tables/Table.php');

Class {$this->table} EXTENDS Table  {

    const FILTER_TYPE_NONE = 0;
    const FILTER_TYPE_BOOL = 1;
    const FILTER_TYPE_INT = 2;
    const FILTER_TYPE_FLOAT = 3;
    const FILTER_TYPE_STRING = 4;

    const ARRAY_TYPE_NUMERIC = 1;
    const ARRAY_TYPE_ASSOC = 2;
    const ARRAY_TYPE_BOTH = 3;

    const CHAR_ESCAPE_FIELD_VALUE = "{$this->char_escapeValue}" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "{$this->char_escapeNamePre}";
    const CHAR_ESCAPE_FIELD_NAME_POST = "{$this->char_escapeNamePost}";

{$this->GetDeclaration_Members()}

{$this->GetDeclaration_TableMetadata()}

{$this->GetDeclaration_Load()}

{$this->GetDeclaration_Save()}

{$this->GetDeclaration_ArrayOfFieldValues()}

{$this->GetDeclaration_BoundParamString()}

{$this->GetDeclaration_ReturnFormattedData()}

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
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_DEFAULT, 4);
        $output .= $this->ColumnifyString(METADATA_FIELDNAME_EXTRA, 4);
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
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_DEFAULT], 4);
            $output .= $this->ColumnifyString($column[METADATA_FIELDNAME_EXTRA], 4);
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
        \$pk_boundParamType = \$this->GetBoundParamTypeString('$_fieldName');
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

        $boundParamAddition = ($this->dbType=="MySQL") ? '$currentRecord_numeric = array_unshift($currentRecord_numeric,$this->GetBoundParamTypeString($listOfFields));' : "";

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
    if (\$listOfFields=='*')
        \$listOfFields=\$this->allFieldsWithoutKeys;
       \$db = get_db_connection();
       \$currentRecord_numeric = \$this->GetArrayOfFieldValues(\$listOfFields, \$this::ARRAY_TYPE_NUMERIC, false, false, true, true);
       $boundParamAddition
       if (empty(\$this->$_fieldName)) {
           \$sql = 'INSERT INTO {$this->char_escapeNamePre}$_tableName{$this->char_escapeNamePost}'.
            ' ({$this->char_escapeNamePre}'.implode('{$this->char_escapeNamePost}, {$this->char_escapeNamePre}', \$listOfFields ).'{$this->char_escapeNamePost})' .
            ' VALUES ('. str_repeat ( '?,' , count(\$listOfFields)-1) .'?) ';
			\$rs = \$db->query(\$sql, null, null, \$currentRecord_numeric);
			if (\$rs) {
				\$this->$_fieldName = \$db->insertID();
				return true;
			} else {
				return false;
			}
        }else{
            \$sql = 'UPDATE {$this->char_escapeNamePre}$_tableName{$this->char_escapeNamePost} SET ' .
            '{$this->char_escapeNamePre}'.implode('{$this->char_escapeNamePost}=?, {$this->char_escapeNamePre}', \$listOfFields ) . '{$this->char_escapeNamePost}=? ' .
'   WHERE {$this->char_escapeNamePre}$_fieldName{$this->char_escapeNamePost} = ?';
        \$currentRecord_numeric[] = $_fieldValue;
        \$rs = \$db->query(\$sql, null, null, \$currentRecord_numeric);
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
            '    private function GetTableMetaAsAssocArray(){' . PHP_EOL .
            '        $record = array(' .PHP_EOL ;
        $countFields = count($this->columns);
        foreach($this->columns as $fieldNum => $field){
            $boolQuoteWhenPopulating = !in_array($field[METADATA_FIELDNAME_TYPE], $this->dataTypes_numeric) ? 1 : 0;
            $filterTypeNum = $this->GetFilterTypeNum($field[METADATA_FIELDNAME_TYPE]);

            $comma = $fieldNum < $countFields - 1 ? ',' : '' ;
            $thisField = $this->ColumnifyString("'{$field[METADATA_FIELDNAME_FIELD]}'=>" , $widthInTabStops)  ;
            $thisValue = $this->ColumnifyString('array(' , 3);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_TYPE.'"=>\''. addslashes("{$field[METADATA_FIELDNAME_TYPE]}") . "'," , $widthInTabStops + 2);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_NULL.'"=>\''."{$field[METADATA_FIELDNAME_NULL]}'," , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_KEY.'"=>\''."{$field[METADATA_FIELDNAME_KEY]}'," , $widthInTabStops);
            $thisValue .= $this->ColumnifyString('"'.METADATA_FIELDNAME_NUMERIC.'"=>\''."{$field[METADATA_FIELDNAME_NUMERIC]}'," , $widthInTabStops);
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


    public function GetDeclaration_ArrayOfFieldValues(){
        $keyColumns = "'" . implode('\',\'' , $this->keyColumnNames) . "'";
        return <<<ARRAY_DECLARATION
    /**
     * This function is primarily only invoked privately
     * Its primary purpose is to return a list of values when given a list of field names
     * It contains a number of options that can be set via parameters
     * @param string \$listOfFields
     * @param int \$arrayType
     * @param bool \$boolUseSanitizeFilters
     * @param bool \$boolEncapsulateInQuotes
     * @param bool \$boolIncludeEmpties
     * @param bool \$boolIncludeNulls
     * @return array
     */
	public function GetArrayOfFieldValues(\$listOfFields='*', \$arrayType=$this->table::ARRAY_TYPE_ASSOC, \$boolUseSanitizeFilters=false, \$boolEncapsulateInQuotes=false, \$boolIncludeEmpties=true, \$boolIncludeNulls=true){
		if (\$listOfFields=='*')
			\$listOfFields=\$this->allFieldsWithoutKeys;
        \$tableMeta=\$this->GetTableMetaAsAssocArray();
		\$result = array();
        \$i = -1;
		foreach (\$listOfFields as \$myIndex=>\$fieldName) {
			if (property_exists(\$this, \$fieldName)) {
				\$myValue=\$this->\$fieldName;
                \$myMeta=\$tableMeta[\$fieldName];
				\$boolIsNull = is_null(\$myValue);
				\$boolIsEmpty = ( isset(\$myValue) && empty(\$myValue) ) && ( \$myValue !== FALSE && \$myValue !== 0 && \$myValue !== 0.0 && \$myValue !== array() );
				\$boolExcludeMe = (!\$boolIncludeEmpties && \$boolIsEmpty) || (!\$boolIncludeNulls && \$boolIsNull);
				if(!\$boolExcludeMe){
					\$i++;
					if(\$arrayType==\$this::ARRAY_TYPE_ASSOC || \$arrayType==\$this::ARRAY_TYPE_BOTH){
						\$result[\$fieldName]=\$this->ReturnFormattedData(\$myValue,\$myMeta,\$boolUseSanitizeFilters,\$boolEncapsulateInQuotes);
					}
					if(\$arrayType==\$this::ARRAY_TYPE_NUMERIC || \$arrayType==\$this::ARRAY_TYPE_BOTH){
						\$result[\$i]=\$this->ReturnFormattedData(\$myValue,\$myMeta,\$boolUseSanitizeFilters,\$boolEncapsulateInQuotes);
					}
				}
			}
		}
		return \$result;
	}
ARRAY_DECLARATION;

    }

    public function GetDeclaration_BoundParamString(){
        $array_key_name = METADATA_FIELDNAME_BOUNDPARAMTYPE;
        return <<<FUNCTION_DECLARATION
    /**
     * This function is to be used when a MySQL database is the source of data
     * It returns bound parameter types to be used to virtually accomplish parameterized querying
     * @param string \$listOfFields
     * @return string
     */
    public function GetBoundParamTypeString(\$listOfFields='*')
    {
        if (\$listOfFields == '*')
            \$listOfFields = \$this->allFieldsWithoutKeys;
        \$myMeta = \$this->GetTableMetaAsAssocArray();
        \$boundParamString = '';
        foreach (\$listOfFields as \$field) {
            if (array_key_exists(\$field, \$myMeta)) {
                \$boundParamString .= \$myMeta[\$field]['$array_key_name'];
            }
        }
        return \$boundParamString;
    }
FUNCTION_DECLARATION;

    }


    public function GetDeclaration_ReturnFormattedData(){
        return <<<'FORMAT_DATA'
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

			$fieldValue = ($boolIsNumeric && !is_numeric($fieldValue)) ? null : $fieldValue;
            $fieldValue = $escapeChar.$fieldValue.$escapeChar ;
            return $fieldValue;
    }
FORMAT_DATA;

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