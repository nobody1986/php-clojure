<?php
namespace clojure;

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

class Boolean extends Type {

    function to_string() {
//        return $this->value . '';
    }

}

class Nil extends Type {

    function to_string() {
        return 'Nil';
    }

}

class Lambda extends Type {

    protected $arglist;
    protected $body;

    function __construct($arglist, $body) {
        $this->arglist = $arglist;
        $this->body = $body;
    }

    function to_string() {
//        return $this->value . '';
        return "<CLOSURE>";
    }

}

class Func extends Type {

    protected $arglist;
    protected $body;
    protected $type;

    function __construct($type, $arglist, $body) {
        $this->type = $type;
        $this->arglist = $arglist;
        $this->body = $body;
    }

    function isNative() {
        return $this->type == 'native';
    }

    function to_string() {
        if ($this->isNative()) {
            return "<FUNC>";
        } else {
            return "<FUNC>";
        }
    }

    function call($args, $env, $local_env) {
        if ($this->isNative()) {
            return call_user_func($this->body, [$args, $env, $local_env]);
        } else {
            foreach ($this->arglist as $key => $arg) {
                $local_env[$arg] = $args[$key];
            }
            return $env->eval2($this->body, $local_env, false, 0);
        }
    }

}
