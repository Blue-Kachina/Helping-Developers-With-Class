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
{$this->GetDeclaration_Load()}
}
CLASS_DECLARATION;
    }

    public function GetDeclaration_Members(){
        $widthInTabStops = 10;

        //Template the member declaration column headers
        $output = PHP_EOL;
        $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('//            ' . 'Field', $widthInTabStops);
        $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('Type', $widthInTabStops);
        $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('Null', $widthInTabStops);
        $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('Key', $widthInTabStops);
        $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('Default', $widthInTabStops);
        $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('Extra', $widthInTabStops);
        $output .= PHP_EOL;
        $output .= '//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
        $output .= PHP_EOL;

        foreach($this->columns as $index => $column) {
            $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs('    public $' . $column["Field"], $widthInTabStops);
            $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs($column['Type'], $widthInTabStops);
            $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs($column['Null'], $widthInTabStops);
            $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs($column['Key'], $widthInTabStops);
            $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs($column['Default'], $widthInTabStops);
            $output .= $this->Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs($column['Extra'], $widthInTabStops);
            $output .= PHP_EOL;
        }
        return $output;
    }

    public function GetDeclaration_Load(){
        $output = '';
        if(!empty($this->keyColumnIndexes)) {
            //the following variable will only work properly on tables that have a single, solitary primary key
            $fieldName = $this->columns[$this->keyColumnIndexes[0]]['Field'];

            $output = '    public function load($param_' . $fieldName . ') {' . PHP_EOL;
            $output .= '        $db = get_db_connection();' . PHP_EOL;
            $output .= '        $sql = \'SELECT * FROM [' . $this->table . '] WHERE [' . $fieldName . '] = ?\';' . PHP_EOL;
            $output .= '        $rs = $db->query($sql, null, null, array($param_' . $fieldName . '));' . PHP_EOL;
            $output .= '' . PHP_EOL;
            $output .= '        if($rs && $rs->rowCount() > 0) {' . PHP_EOL;
            $output .= '            $row = $rs->fetch(CoreDB::FETCH_ASSOC);' . PHP_EOL;
            $output .= '            $this->loadFromArray($row);' . PHP_EOL;
            $output .= '        }' . PHP_EOL;
            $output .= '    }';
        }
        return $output;
    }

    private static function Make_String_N_Tab_Stops_Wide_By_PostFixing_Tabs($myString, $nTabStops){
        $numRepetitionsGuess =$nTabStops - (floor(strlen($myString)/4)) ;
        $numRepetitions= $numRepetitionsGuess >0 ? $numRepetitionsGuess : 0 ;
        $myTrailingTabSpace = str_repeat("\t", $numRepetitions );
        return $myString . $myTrailingTabSpace;
    }

}