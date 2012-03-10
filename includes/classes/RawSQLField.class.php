<?php
class RawSQLField {
    private $value;
    private $param;

    public function __construct($value) {
        $this->value = $value;
    }

    public function value() {
        return $this->value;
    }

    public function set_param($param) {
        $this->param = $param;
    }

    public function param() {
        return $this->param;
    }
}