<?php

class CoreValidateFloat extends CoreValidateRule {

    private $name;
    private $label;

    public function __construct($name, $label) {
        $this->name = $name;
        $this->label = $label;
    }

    public function validate() {
        $value = $_POST[$this->name];
        return $value == '' || filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    public function get_validator_js() {
        return '';
    }

    public function get_client_rule() {
        return 'number: true';
    }

    public function get_client_message() {
        return 'number: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '';
    }

    public function get_error_message() {
        return $this->label . ' must be a number.';
    }
}