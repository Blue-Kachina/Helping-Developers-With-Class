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
    private $autoIncrementingKeys = array();
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

        if(array_key_exists(METADATA_FIELDNAME_KEY,$column) && (strtoupper($column[METADATA_FIELDNAME_EXTRA])=='AUTO_INCREMENT')){
            $this->autoIncrementingKeys[]=($column[METADATA_FIELDNAME_FIELD]) ;
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

        $version = getVersionInfo();



        return <<<CLASS_DECLARATION
<?php
/**
 * Created by "Helping Developers With Class".
 * Version: {$version['version']}
 * GIT: {$version['git']}
 * User: $currentUser
 * Class Creation: $currentDateTime
 */
require_once(DIR_ROOT . '/lib/classes/tables/GeneratedClass.php');

Class {$this->table} EXTENDS GeneratedClass  {
    const CHAR_ESCAPE_FIELD_VALUE = "{$this->char_escapeValue}" ;
    const CHAR_ESCAPE_FIELD_NAME_PRE = "{$this->char_escapeNamePre}";
    const CHAR_ESCAPE_FIELD_NAME_POST = "{$this->char_escapeNamePost}";
    
    protected \$fields_excluded_locally = array();

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//BEGIN UPDATEABLE SECTION
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
{$this->GetDeclaration_UpdateablePart()}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//END UPDATEABLE SECTION
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public function load(\$primaryKeyData){
        parent::load(\$primaryKeyData);
    }

    public function save(\$listOfFields = "*"){
        parent::save(\$listOfFields);
    }
}
CLASS_DECLARATION;
    }


    public function GetDeclaration_UpdateablePart(){
        return $this->GetDeclaration_Members() . PHP_EOL . $this->GetDeclaration_TableMetadata();
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
        $output = "    public \$TABLENAME = \"{$this->table}\";" . PHP_EOL;
        $output .="    public \$PRIMARYKEY = array(\"" . implode('","', $this->keyColumnNames) . "\");" . PHP_EOL;
        $output .="    public \$AUTOINCREMENT = array(\"" . implode('","', $this->autoIncrementingKeys) . "\");" .PHP_EOL;
        $output .= PHP_EOL . PHP_EOL;
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
        $output .= '//          ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
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
        //$output .= '    public $allFieldsWithoutKeys = array(\'' . implode('\', \'', $fieldsWithoutKeys) . '\');' . PHP_EOL;
        $output .= PHP_EOL;

        return $output;
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
            '    * Returns an associative array containing metadata about the fields in the table that this class describes' . PHP_EOL .
            '    * @return array' . PHP_EOL .
            '    */' . PHP_EOL .
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

function getVersionInfo(){

    $major = 1;
    $minor = 1;
    $patch = 1;

    $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
    $commitDate = exec('git log -n1 --pretty=%ci HEAD');

    $return = array(
        'version'=>"$major.$minor.$patch-dev",
        'git'=>"$commitHash - $commitDate"
        ) ;
    return $return;
}