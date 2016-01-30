<?php

class CoreValidatePhone extends CoreValidateRule {

    const REGEX_NORMAL = '/^\(?[0-9]{3}\)? *-? *[0-9]{3} *-? *[0-9]{4}$/';
    const REGEX_INTERNATIONAL = '/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/';

    private $name;
    private $label;
    private $is_international;

    public function __construct($name, $label, $is_international = false) {
        $this->name = $name;
        $this->label = $label;
        $this->is_international = $is_international;
    }

    public function validate() {
        $value = $_POST[$this->name];
        return $value == '' || preg_match($this->is_international ? self::REGEX_INTERNATIONAL : self::REGEX_NORMAL, $value);
    }

    public function get_validator_js() {
        return '

            $.validator.addMethod("phone", function(value, element) {
                return this.optional(element) ||
                value.match(' . self::REGEX_NORMAL . ');
            }, "Please enter a valid phone number");


            $.validator.addMethod("intlphone", function(value, element) {
                return this.optional(element) ||
                value.match(' . self::REGEX_INTERNATIONAL . ');
            }, "Please enter a valid phone number");

        ';
    }

    public function get_client_rule() {
        return ($this->is_international ? 'intlphone' : 'phone') . ': true';
    }

    public function get_client_message() {
        return ($this->is_international ? 'intlphone' : 'phone') . ': "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return $this->is_international ? '' : '$("input[name=' . $this->name . ']").mask("(999) 999-9999");';
    }

    public function get_error_message() {
        return $this->label . ' must be a valid phone number.';
    }
}