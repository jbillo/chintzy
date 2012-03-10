<?php
/**
 * Field primitive for a != operation in a query.
 * @author jbillo
 *
 */
class NotField {
    private $val;

    function __construct($value) {
        $this->val = $value;
    }

    function value() {
        return $this->val;
    }
}