<?php

class CoreValidateFile extends CoreValidateRule {

    private $name;
    private $label;
    private $maximum_size;
    private $allowed_extensions;

    /**
     * @param $name string Name of the file input
     * @param $label string Label of the file input
     * @param $maximum_size int Maximum size in bytes
     * @param $allowed_extensions array
     */
    public function __construct($name, $label, $maximum_size, $allowed_extensions) {
        $this->name = $name;
        $this->label = $label;
        $this->maximum_size = $maximum_size;
        $this->allowed_extensions = $allowed_extensions;
    }

    private function validate_file_size() {
        if(is_null($this->maximum_size)) {
            return true;
        }

        $file = $_FILES[$this->name];
        $file_size = filesize($file['tmp_name']);
        return $file_size <= $this->maximum_size;
    }

    private function validate_file_extension() {
        if(is_null($this->allowed_extensions) || !count($this->allowed_extensions)) {
            return true;
        }

        $file = $_FILES[$this->name];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        return in_array($extension, $this->allowed_extensions);
    }

    public function validate() {
        return $this->validate_file_size() && $this->validate_file_extension();
    }

    public function get_validator_js() {
        return '

            $.validator.addMethod("filetype", function(value, element, allowed_file_extensions) {
                if(!value) {
                    return true;
                }
                var extension = value.substring(value.lastIndexOf(".") + 1);
                return allowed_file_extensions.indexOf(extension) >= 0;
            }, "Invalid file type");

            $.validator.addMethod("filesize", function(value, element, size) {
                if (!window.FileReader || !element.files || !element.files[0]) {
                    return true;
                }
                return element.files[0].size < size;
            }, "File exceeds maximum file size allowed.");

        ';
    }

    public function get_client_rule() {
        $rules = array();

        if(!is_null($this->maximum_size)) {
            $rules[] = 'filesize: ' . $this->maximum_size;
        }

        if(!is_null($this->allowed_extensions) && count($this->allowed_extensions)) {
            $rules[] = 'filetype: ["' . implode('", "', $this->allowed_extensions) . '"]';
        }

        return implode(', ', $rules);
    }

    public function get_client_message() {
        $messages = array();

        if(!is_null($this->maximum_size)) {
            $messages[] = 'filesize: "' . $this->get_error_file_size() . '"';
        }

        if(!is_null($this->allowed_extensions) && count($this->allowed_extensions)) {
            $messages[] = 'filetype: "' . $this->get_error_file_extension() . '"';
        }

        return implode(', ', $messages);
    }

    public function get_client_js() {
        return '';
    }

    private function get_error_file_size() {
        return $this->label . ' exceeds the maximum file size of ' . $this->maximum_size . ' bytes.';
    }

    private function get_error_file_extension() {
        return $this->label . ' must be a file of one of the following types: ' . implode(', ', $this->allowed_extensions);
    }

    public function get_error_message() {
        $error = '';

        if(!$this->validate_file_size()) {
            $error .= $this->get_error_file_size();
        } elseif(!$this->validate_file_extension()) {
            $error .= $this->get_error_file_extension();
        }

        return $error;
    }

}