<?php

class CoreValidateEqualTo extends CoreValidateRule {

    private $field1_name;
    private $field1_label;
    private $field2_name;
    private $field2_label;

    public function __construct($field1_name, $field1_label, $field2_name, $field2_label) {
        $this->field1_name = $field1_name;
        $this->field1_label = $field1_label;
        $this->field2_name = $field2_name;
        $this->field2_label = $field2_label;
    }

    public function validate() {
        $value1 = $_POST[$this->field1_name];
        $value2 = $_POST[$this->field2_name];
        return $value1 == $value2;
    }

    public function get_validator_js() {
        return '';
    }

    public function get_client_rule() {
        return 'equalTo: "input[name=' . $this->field2_name . ']"';
    }

    public function get_client_message() {
        return 'equalTo: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '';
    }

    public function get_error_message() {
        return $this->field1_label . ' must be equal to ' . $this->field2_label . '.';
    }
}