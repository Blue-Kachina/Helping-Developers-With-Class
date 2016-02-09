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
        if (array_key_exists('Key',$column) && strtoupper($column['Key'])=='PRI'){
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
        $output .= $this->ColumnifyString('//          ' . 'Field', $widthInTabStops);
        $output .= $this->ColumnifyString('Type', $widthInTabStops);
        $output .= $this->ColumnifyString('Null', 4);
        $output .= $this->ColumnifyString('Key', 4);
        $output .= $this->ColumnifyString('Default', 4);
        $output .= $this->ColumnifyString('Extra', 4);
        $output .= PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
        $output .= PHP_EOL;

        //Member definitions (and metadata added into inline comments)
        foreach($this->columns as $index => $column) {
            $output .= $this->ColumnifyString('    public $' . $column["Field"] . ';' , $widthInTabStops);
            $output .= $this->ColumnifyString('//' . $column['Type'], $widthInTabStops);
            $output .= $this->ColumnifyString($column['Null'], 4);
            $output .= $this->ColumnifyString($column['Key'], 4);
            $output .= $this->ColumnifyString($column['Default'], 4);
            $output .= $this->ColumnifyString($column['Extra'], 4);
            $output .= PHP_EOL;
        }

        //A message to alert developers who might use this class.  Any non-field related properties that they might add to this class should be added the following comment.  Doing so will allow for easy updates to this class using this utility at a later time
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;
        $output .= '//If you create any properties that aren\'t associated with a field from this table, please define them underneath this line'. PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~' .PHP_EOL;
        return $output;
    }

    public function GetDeclaration_Load(){
        $output = '';
        if(!empty($this->keyColumnIndexes)) {
            //the following variable will only work properly on tables that have a single, solitary primary key
            $fieldName = $this->columns[$this->keyColumnIndexes[0]]['Field'];

            $output =   '    public function load($param_' . $fieldName . ') {' . PHP_EOL .
                        '        $db = get_db_connection();' . PHP_EOL .
                        '        $sql = \'SELECT * FROM [' . $this->table . '] WHERE [' . $fieldName . '] = ?\';' . PHP_EOL .
                        '        $rs = $db->query($sql, null, null, array($param_' . $fieldName . '));' . PHP_EOL .
                        '' . PHP_EOL .
                        '        if($rs && $rs->rowCount() > 0) {' . PHP_EOL .
                        '            $row = $rs->fetch(CoreDB::FETCH_ASSOC);' . PHP_EOL .
                        '            $this->loadFromArray($row);' . PHP_EOL .
                        '        }' . PHP_EOL .
                        '    }' .PHP_EOL;
        }
        return $output;
    }

    public function GetDeclaration_Save(){
        $_currentRecord='$currentRecord';

        $template =
'    public function save() {' .PHP_EOL .
'       $db = get_db_connection();' . PHP_EOL .
'       $currentRecord = $this->GetAssocArrayFromListOfFields();' . PHP_EOL .
'       if (empty($this->' . $this->columns[$this->keyColumnIndexes[0]]['Field'] .  ')) {' . PHP_EOL .
'           $sql = \'INSERT INTO [' . $this->table . ']\'.' . PHP_EOL . <<<COLUMN_IMPLOSION
            ' (['.implode('], [', array_keys($_currentRecord)).'])' .
            ' VALUES ('.implode(', ', $_currentRecord).') ';
COLUMN_IMPLOSION;

        $tableName = $this->table ;
        $pkName = $this->columns[$this->keyColumnIndexes[0]]['Field'] ;

        $template.= PHP_EOL .
            '			$rs = $db->query($sql, null, null, array_keys($currentRecord));' . PHP_EOL .
            '			if ($rs) {'  . PHP_EOL .
            '				$this->' . $pkName . ' = $db->insertID();'  . PHP_EOL .
            '				get_msg_system()->addMessage(\'' .  $tableName . '\' . $this->' . $pkName . '. \' Saved Successfully.\', Msg::GOOD);'  . PHP_EOL .
            '				return true;' . PHP_EOL .
            '			} else {' .  PHP_EOL .
            '				get_msg_system()->addMessage(\'' . $tableName . '\' . $this->' . $pkName . ' . \' Save Failed. \' . $db->errorMsg(), Msg::ERROR);' . PHP_EOL .
            '				return false;'  . PHP_EOL .
            '			}' . PHP_EOL .
            '        }else{' .PHP_EOL .
            '            $sql = \'UPDATE [' . $this->table .  '] SET \' .' . PHP_EOL .<<<COLUMN_IMPLOSION
            '['.implode('], [', array_keys($_currentRecord)) . '] = ?' .
COLUMN_IMPLOSION;
                 $template .= PHP_EOL . '\'   WHERE [' . $pkName . '] = ?\'; ' .PHP_EOL .
                '        $rs = $db->query($sql, null, null, $currentRecord);' . PHP_EOL .
                '        if ($rs) {' . PHP_EOL .
                 '            $this->' . $pkName . ' =  $db->insertID();' . PHP_EOL .
                '            get_msg_system()->addMessage(\'' . $tableName . ' \' . $this->' .  $pkName .  ' . \' Updated Successfully.\', Msg::GOOD); ' . PHP_EOL .
                    '            return true;' . PHP_EOL .
                '        } else {' . PHP_EOL .
                    '            get_msg_system()->addMessage(\'' . $tableName .  ' \' . $this->' . $pkName  .  ' . \' Update Failed. \' . $db->errorMsg(), Msg::ERROR);' . PHP_EOL .
                    '            return false;' . PHP_EOL .
                '        }' . PHP_EOL .
            '    }' . PHP_EOL .
    '}' . PHP_EOL ;


        return $template;
    }

    public function GetDeclaration_TableMetadata(){
        $widthInTabStops = 10;
        $template =
            '    private function GetTableMetaAsAssocArray(){' . PHP_EOL .
            '        $record = array(' .PHP_EOL ;
        $countFields = count($this->columns);
        foreach($this->columns as $fieldNum => $field){
            $comma = $fieldNum < $countFields - 1 ? ',' : '' ;
            $thisField = $this->ColumnifyString("'{$field['Field']}'=>" , $widthInTabStops)  ;
            $thisValue = $this->ColumnifyString('array(' , 3);
            $thisValue .= $this->ColumnifyString('"Type"=>\''. addslashes("{$field['Type']}") . "'," , $widthInTabStops + 5);
            $thisValue .= $this->ColumnifyString('"Null"=>\''."{$field['Null']}'," , 4);
            $thisValue .= $this->ColumnifyString('"Key"=>\''."{$field['Key']}'," , 4);
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
        $fieldArray = 'array(\'' . implode('\', \'', array_column($this->columns, 'Field')) . '\')';
        $template =
        "public function GetAssocArrayFromListOfFields(".'$listOfFields'."=$fieldArray){" . PHP_EOL .
            '$result = array();' . PHP_EOL .
            'foreach($listOfFields as $fieldName){' . PHP_EOL .
                'if(property_exists($this,$fieldName)){' . PHP_EOL .
                    '$result[$fieldName]=$this->FilterAndEscapeField($fieldName);' . PHP_EOL .
                '}' . PHP_EOL .
            '}' . PHP_EOL .
            'return $result;' . PHP_EOL .
        '}' . PHP_EOL ;
        return $template;
    }

    public function GetDeclaration_FilterAndEscape(){
        return <<<'FILTER_FUNCTION'
    public function FilterAndEscapeField($fieldName){
        if(property_exists($this,$fieldName)){
            $tableMeta = $this->GetTableMetaAsAssocArray();

            $filterType = $tableMeta[$fieldName]['FilterTypeNum'];
            $boolAllowsNull = $tableMeta[$fieldName]['Null'] == 'YES' ? true : false ;
            $boolRequiresEscape = $tableMeta[$fieldName]['BoolEscapeSQLFieldName'];

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
FILTER_FUNCTION;

    }


    private static function ColumnifyString($myString, $columnWidthInTabStops){
        $numRepetitionsGuess =$columnWidthInTabStops - (floor(strlen($myString)/4)) ;
        $numRepetitions= $numRepetitionsGuess >0 ? $numRepetitionsGuess : 0 ;
        $myTrailingTabSpace = str_repeat("\t", $numRepetitions );
        return $myString . $myTrailingTabSpace;
    }

}