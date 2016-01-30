<?php

class CoreValidateZipCode extends CoreValidateRule {

    const REGEX = '/\d{5}-\d{4}$|^\d{5}$/';

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

            $.validator.addMethod("zip", function(value, element) {
                return this.optional(element) ||
                value.match(' . self::REGEX . ');
            }, "Please enter a valid zip code");

        ';
    }

    public function get_client_rule() {
        return 'zip: true';
    }

    public function get_client_message() {
        return 'zip: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '$("input[name=' . $this->name . ']").mask("99999");';
    }

    public function get_error_message() {
        return $this->label . ' must be a valid 5 digit zip code.';
    }

}