<?php

class CoreValidatePostalCode extends CoreValidateRule {

    const REGEX = '/^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$/i';

    private $name;
    private $label;

    public function __construct($name, $label) {
        $this->name = $name;
        $this->label = $label;
    }

    public function validate() {
        $value = $_POST[$this->name];
        return $value == '' || preg_match(self::REGEX, $value);
    }

    public function get_validator_js() {
        return '

            $.validator.addMethod("postalcode", function(value, element) {
                return this.optional(element) ||
                value.match(' . self::REGEX . ');
            }, "Please enter a valid postal code");

        ';
    }

    public function get_client_rule() {
        return 'postalcode: true';
    }

    public function get_client_message() {
        return 'postalcode: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '$("input[name=' . $this->name . ']").mask("a9a 9a9");';
    }

    public function get_error_message() {
        return $this->label . ' must be a valid 6 character postal code.';
    }
}