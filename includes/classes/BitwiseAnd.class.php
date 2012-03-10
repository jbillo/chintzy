<?php
/**
 * Field primitive for a bitwise AND comparison with ADODB.
 * @author jbillo
 * @version 20081218
 */
class BitwiseAnd {
    /**
     * Value for the bitwise AND operation.
     *
     * @var int
     */
    private $val;

    /**
     * Initializes a new bitwise AND comparison operator.
     * This is primarily used in the internal _parse_conditions function for detecting conditionals
     * against stored bitmasks.
     *
     * @param int $value
     */
    public function __construct($value) {
        $this->val = $value;
    } // function

    /**
     * Returns the value stored for this bitwise AND.
     *
     * @return int
     */
    public function value() {
        return $this->val;
    } // function

}