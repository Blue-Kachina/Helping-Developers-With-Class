<?php
/*
 * Created to mimic ADOFieldObject
 */
class CoreFieldObject{
    public $name;
    public $type;
    public $max_length;

    public function __construct($name, $type, $max_length){
        $this->name = $name;
        $this->type = $type;
        $this->max_length = $max_length;
    }
}

?>