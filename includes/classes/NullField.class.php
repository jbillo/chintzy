<?php
class NullField {
    // This class is used internally to represent NULL fields.
    // This way, generic functions do not confuse a user input of "NULL" with a database field that should be null.
}