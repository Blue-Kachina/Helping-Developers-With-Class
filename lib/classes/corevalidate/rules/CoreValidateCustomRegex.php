<?php

class CoreValidateCustomRegex extends CoreValidateRule {

    private $name;
    private $label;
    private $regex;
    private $error_message;

    public function __construct($name, $label, $regex, $error_message) {
        $this->name = $name;
        $this->label = $label;
        $this->regex = $regex;
        $this->error_message = $error_message;
    }

    public function validate() {
        $value = $_POST[$this->name];
        return $value == '' || preg_match($this->regex, $value);
    }

    public function get_validator_js() {
        return '

            $.validator.addMethod("customRegex' . $this->getUniqueRuleID() . '", function(value, element) {
                return this.optional(element) ||
                value.match(' . $this->regex . ');
            }, "Please enter a valid email");

        ';
    }

    public function get_client_rule() {
        return 'customRegex' . $this->getUniqueRuleID() . ': true';
    }

    public function get_client_message() {
        return 'customRegex' . $this->getUniqueRuleID() . ': "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '';
    }

    public function get_error_message() {
        return $this->error_message;
    }

    public function getUniqueRuleID() {
        return md5($this->regex);
    }
}