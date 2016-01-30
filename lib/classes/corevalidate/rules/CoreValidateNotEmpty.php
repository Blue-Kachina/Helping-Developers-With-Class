<?php

class CoreValidateNotEmpty extends CoreValidateRule {

    private $name;
    private $label;

    public function __construct($name, $label) {
        $this->name = $name;
        $this->label = $label;
    }

    public function validate() {
        $value = $_POST[$this->name];
        return !empty($value);
    }

    public function get_validator_js() {
        return '';
    }

    public function get_client_rule() {
        return 'required: true';
    }

    public function get_client_message() {
        return 'required: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '';
    }

    public function get_error_message() {
        return $this->label . ' is a required field. Please enter a value.';
    }
}