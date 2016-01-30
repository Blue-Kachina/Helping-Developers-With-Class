<?php

class CoreValidateEmail extends CoreValidateRule {

    const REGEX = '/(^[a-zA-Z]([a-zA-Z0-9]|_(?=[a-zA-Z0-9]))*(\.(?=[a-zA-Z0-9])([a-zA-Z0-9]|_(?=[a-zA-Z0-9]))*)?[a-zA-Z])@[a-zA-Z0-9]+\.[a-zA-Z]+$/';

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

            $.validator.addMethod("validEmail", function(value, element) {
                return this.optional(element) ||
                value.match(' . self::REGEX . ');
            }, "Please enter a valid email");

        ';
    }

    public function get_client_rule() {
        return 'validEmail: true';
    }

    public function get_client_message() {
        return 'validEmail: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '';
    }

    public function get_error_message() {
        return $this->label . ' must be a valid email address.';
    }
}