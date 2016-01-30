<?php

abstract class CoreValidateRule {

    /**
     * @return bool
     */
    public abstract function validate();

    /**
     * @return string
     */
    public abstract function get_error_message();

    /**
     * @return string
     */
    public abstract function get_client_rule();

    /**
     * @return string
     */
    public abstract function get_client_message();

    /**
     * @return string
     */
    public abstract function get_client_js();

    /**
     * @return string
     */
    public abstract function get_validator_js();

    /**
     * Used to uniquely identify a rule so that the rule validator code is not echo'd twice onto the page
     *
     * @return string
     */
    public function getUniqueRuleID() {
        return get_called_class();
    }
}