<?php

class CoreValidateException extends Exception {
    public $name;
    public $label;
    public $rule;

    /**
     * @param string $name
     * @param CoreValidateRule $rule
     * @param string $message
     */
    public function __construct($name, $rule, $message) {
        $this->name = $name;
        $this->rule = $rule;

        parent::__construct($message);
    }
}