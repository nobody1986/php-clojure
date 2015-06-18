
<?php
namespace \clojure\lang;
abstract class Type {

    protected $value;

    function __construct($value) {
        $this->value = $value;
    }

    function get_class() {
        return __CLASS__;
    }

    abstract function to_string();

    function get_value() {
        return $this->value;
    }

}

class Double extends Type {

    function to_string() {
        return $this->value . '';
    }

}

class Integer extends Type {

    function to_string() {
        return $this->value . '';
    }

}

class Ratio extends Type {

    function to_string() {
        return $this->value[0] . '/' . $this->value[1];
    }

}

class Character extends Type {

    function to_string() {
        return $this->value;
    }

}

class CString extends Type {

    function to_string() {
        return $this->value . '';
    }

}

class CList extends Type {

    function to_string() {
//        return $this->value . '';
    }

}

class Set extends Type {

    function to_string() {
//        return $this->value . '';
    }

}

class Map extends Type {

    function to_string() {
//        return $this->value . '';
    }

}

class Vector extends Type {

    function to_string() {
//        return $this->value . '';
    }

}
