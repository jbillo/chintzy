<?php
class InField {
    private $values;

    public function __construct($vals) {
        $this->values = $vals;
    } 

    public function values() {
        return $this->values;
    } 
} 