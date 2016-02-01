<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2016-01-29
 * Time: 10:41 PM
 */

Class Class_Template {
    var $table;
    var $columns = array();
    var $keyColumnIndexes = array();

    function __construct($param_table, $param_columns=[]){
        $this->table=$param_table;
        foreach ($param_columns as $columnIndex => $columnName){
            $this->AddColumn($columnName);
        }
    }

    function AddColumn($columnName){
        $arraySize = count($this->columns);
        $nextIndex = $arraySize ? $arraySize : 0 ;
        $this->columns[]=array("index"=>$nextIndex,"name"=>$columnName);
        //ToDo: Include some code here that will add the column to keyColumnIndexes when applicable
    }

    //ToDo: Include some templates of the various class parts using heredoc

}