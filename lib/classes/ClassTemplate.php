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
{$this->GetDeclaration_Members()}
{$this->GetDeclaration_RecordAsArray()}
{$this->GetDeclaration_Load()}
{$this->GetDeclaration_Save()}
}
CLASS_DECLARATION;
    }

    public function GetDeclaration_Members(){
        $widthInTabStops = 10;

        //Template the member declaration column headers
        $output = PHP_EOL;
        $output .= $this->ColumnifyString('//            ' . 'Field', $widthInTabStops);
        $output .= $this->ColumnifyString('Type', $widthInTabStops);
        $output .= $this->ColumnifyString('Null', $widthInTabStops);
        $output .= $this->ColumnifyString('Key', $widthInTabStops);
        $output .= $this->ColumnifyString('Default', $widthInTabStops);
        $output .= $this->ColumnifyString('Extra', $widthInTabStops);
        $output .= PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
        $output .= PHP_EOL;

        foreach($this->columns as $index => $column) {
            $output .= $this->ColumnifyString('    public $' . $column["Field"], $widthInTabStops);
            $output .= $this->ColumnifyString($column['Type'], $widthInTabStops);
            $output .= $this->ColumnifyString($column['Null'], $widthInTabStops);
            $output .= $this->ColumnifyString($column['Key'], $widthInTabStops);
            $output .= $this->ColumnifyString($column['Default'], $widthInTabStops);
            $output .= $this->ColumnifyString($column['Extra'], $widthInTabStops);
            $output .= PHP_EOL;
        }

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
                        '    }';
        }
        return $output;
    }

    public function GetDeclaration_Save(){
        $_sql='$sql';
        $_currentRecord='$currentRecord';

        $template =
'    public function save() {' .PHP_EOL .
'       $db = get_db_connection()' . PHP_EOL .
'       $currentRecord = GetThisObjectAsAssocArray(true);' . PHP_EOL .
'       if (empty($this->' . $this->columns[$this->keyColumnIndexes[0]]['Field'] .  ')) {' . PHP_EOL .
'           $sql = \'INSERT INTO [' . $this->table . ']\'.' . PHP_EOL . <<<COLUMN_IMPLOSION
            " ([".implode("], [", array_keys($_currentRecord))."])";
            " VALUES ('".implode("', '", $_currentRecord)."') ";
COLUMN_IMPLOSION;

        $template .= PHP_EOL .
            '		}' . PHP_EOL ;
        return $template;
    }

    public function GetDeclaration_RecordAsArray(){
        $template =
            '    private function GetThisObjectAsAssocArray($boolPopulatedOnly){' . PHP_EOL .
            '        $record = array(' . PHP_EOL ;
        $countFields = count($this->columns);
        //TODo:Discuss with the others the feasibility of using get_object_vars here instead of looping through them all, and individually setting them
        foreach($this->columns as $fieldNum => $field){
            $_this='$this';
            $comma = $fieldNum < $countFields - 1 ? ',' : '' ;
            $thisField = "'{$field['Field']}'=>"  ;
            $thisValue = '$this->'."{$field['Field']}$comma";
            $template .= '			' . $this->ColumnifyString($thisField,10) . $thisValue .PHP_EOL;
        }
        $template .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'.PHP_EOL;
        $template .= '//If you create any properties that aren\'t associated with a field from this table, please define them underneath this line;'.PHP_EOL;
        $template .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'.PHP_EOL;
        $template .=
            '        )' . PHP_EOL .
            '        return $boolPopulatedOnly ? $record : array_filer ( $record , , ARRAY_FILTER_USE_BOTH );' . PHP_EOL .
            '    }';
        return $template;
    }


    private static function ColumnifyString($myString, $columnWidthInTabStops){
        $numRepetitionsGuess =$columnWidthInTabStops - (floor(strlen($myString)/4)) ;
        $numRepetitions= $numRepetitionsGuess >0 ? $numRepetitionsGuess : 0 ;
        $myTrailingTabSpace = str_repeat("\t", $numRepetitions );
        return $myString . $myTrailingTabSpace;
    }

}