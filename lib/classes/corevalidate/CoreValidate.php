<?php
require_once('CoreValidateException.php');
require_once('CoreValidateRule.php');

foreach(glob(__DIR__ . "/rules/*.php") as $rule) {
    include_once($rule);
}

/**
 * Class CoreValidate
 *
 * Required javascript for client side validation:
 * * JQuery
 * * JQuery Validate
 * * page_message_error(string, string) function
 */
class CoreValidate {

    private $locale;
    private $validation_rules = array();

    /**
     * @param string $locale Localization csv file from the locale folder for error message purposes
     */
    public function __construct($locale = 'en_ca') {
        $this->locale = $locale;
    }

    /**
     * @param string $input_name
     * @param array $rules array of CoreValidateRule objects, in the order that they should be evaluated for validation purposes
     */
    public function add_form_validation($input_name, array $rules) {
        $this->validation_rules[$input_name] = $rules;
    }

    public function validate_server() {
        foreach($this->validation_rules as $input_name => $validation_rules) {
            foreach($validation_rules as $rule /* @var $rule CoreValidateRule */) {
                if(!$rule->validate()) {
                    $error_message = $rule->get_error_message();
                    throw new CoreValidateException($input_name, $rule, $error_message);
                }
            }
        }
    }

    /**
     * @param string $form_id id attribute of the form you are validating
     * @return string returns all javascript required to initialize the client-side validation
     * @throws Exception if a rule has not been implemented
     */
    public function validate_client($form_id) {
        $rules = array();
        $messages = array();
        $js = array();
        $validator_methods = array();

        foreach($this->validation_rules as $input_name => $validation_rules) {

            $field_rules = array();
            $field_messages = array();

            foreach($validation_rules as $rule /* @var $rule CoreValidateRule */) {
                $field_rules[] = $rule->get_client_rule();
                $field_messages[] = $rule->get_client_message();
                $js[] = $rule->get_client_js();

                $rule_id = $rule->getUniqueRuleID();

                if(!isset($validator_methods[$rule_id])) {
                    $validator_methods[$rule_id] = $rule->get_validator_js();
                }
            }

            $rules[] = $input_name . ': {' . implode(', ', $field_rules) . '}';
            $messages[] = $input_name . ': {' . implode(', ', $field_messages) . '}';
        }

        return '<script type="text/javascript">

                ' . implode(PHP_EOL, $js) . '

                ' . implode(PHP_EOL, $validator_methods) . '

                $(document).ready(function () {
                    $(\'#' . $form_id . '\').validate({
                        rules: {' . implode(', ' . PHP_EOL, $rules) . '},
                        messages: {' . implode(', ' . PHP_EOL, $messages) . '},
                        errorPlacement: function (error, element) {
                               if (element.attr("type") == "checkbox") {
                                    element.closest("div").css( {border : "1px solid red"});
                               }
                               if (element.hasClass("datepicker")) {
                                    error.insertAfter(element.parent());
                               } else {
                                    error.insertAfter(element);
                               }
                        },
                        invalidHandler: function(e, validator) {
                            var errors = "";
                            for (i = 0; i < validator.errorList.length; ++i) {
                                errors += validator.errorList[i].message + "<br />";
                            }
                            page_message_error("Error", errors);
                        }
                    });
                });
                </script>';
    }
}