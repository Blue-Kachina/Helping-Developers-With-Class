<?php

class CoreValidateDateRange extends CoreValidateRule {

    private $date1_name;
    private $date1_label;
    private $date2_name;
    private $date2_label;

    public function __construct($date1_name, $date1_label, $date2_name, $date2_label) {
        $this->date1_name = $date1_name;
        $this->date1_label = $date1_label;
        $this->date2_name = $date2_name;
        $this->date2_label = $date2_label;
    }

    public function validate() {
        $date1 = $_POST[$this->date1_name];
        $date2 = $_POST[$this->date2_name];
        return ($date1 == '' && $date2 = '') || strtotime($date2) > strtotime($date1);
    }

    public function get_validator_js() {
        return '

            $.validator.addMethod("daterange", function(value, element, otherdate) {
                var d1 = new Date(Date.parse(value));
                var d2 = new Date(Date.parse($(otherdate).val()));
                return d2 > d1;
            }, "Invalid Date Range");

        ';
    }

    public function get_client_rule() {
        return 'daterange: "input[name=' . $this->date2_name . ']"';
    }

    public function get_client_message() {
        return 'daterange: "' . $this->get_error_message() . '"';
    }

    public function get_client_js() {
        return '';
    }

    public function get_error_message() {
        return $this->date1_label . ' must be before ' . $this->date2_label . '.';
    }
}