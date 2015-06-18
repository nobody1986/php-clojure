<?php

namespace clojure;

abstract class Type {

    protected $value;

    function __construct($value) {
        $this->value = $value;
    }

    abstract function to_string();

    function get_value() {
        return $this->value;
    }

    function get_class() {
        return __CLASS__;
    }

}

class Double extends Type {

    function to_string() {
        return $this->value . '';
    }

    function div($obj) {
        $val = $obj->get_value();
        if (is_array($val)) {
            $ret = $this->value / $val[0] / $val[1];
        } else {
            $ret = $this->value / $val;
        }
        return new Double($ret);
    }

    function sub($obj) {
        $val = $obj->get_value();
        if (is_array($val)) {
            $ret = $this->value - $val[0] / $val[1];
        } else {
            $ret = $this->value - $val;
        }
        return new Double($ret);
    }

}

class Integer extends Type {

    function to_string() {
        return $this->value . '';
    }

    function div($obj) {
        $val = $obj->get_value();
        if (is_array($val)) {
            return new Ratio([$this->value * $val[0], $val[1]]);
        } else if (is_integer($val)) {
            return new Ratio([$this->value, $val]);
        } else {
            return new Double($this->value / $val);
        }
    }

    function sub($obj) {
        $val = $obj->get_value();
        if (is_array($val)) {
            return new Ratio([$this->value * $val[1] - $val[0], $val[1]]);
        } else if (is_integer($val)) {
            return new Integer($this->value - $val);
        } else {
            return new Double($this->value - $val);
        }
    }

}

class Ratio extends Type {

    function to_string() {
        return $this->value[0] . '/' . $this->value[1];
    }

    function div($obj) {
        $val = $obj->get_value();
        if (is_array($val)) {
            return new Ratio([$this->value[0] * $val[1], $this->value[1] * $val[0]]);
        } else if (is_integer($val)) {
            return new Ratio([$this->value[0], $this->value[1] * $val]);
        } else {
            return new Double($this->value[0] / $this->value[1] / $val);
        }
    }

    function sub($obj) {
        $val = $obj->get_value();
        if (is_array($val)) {
            return new Ratio([$this->value[0] * $val[1]  -  $this->value[1] * $val[0], $this->value[1] * $val[1]]);
        } else if (is_integer($val)) {
            return new Ratio([$this->value[0] - $this->value[1] * $val, $this->value[1] ]);
        } else {
            return new Double($this->value[0] / $this->value[1] - $val);
        }
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

    function call($args, $env, $local_env) {
        foreach ($this->arglist as $key => $arg) {
            $local_env[$arg] = $args[$key];
        }
        return $env->eval2($this->body, $local_env, false, 0);
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
            $func = $this->body;
            return $func($args, $env, $local_env);
        } else {
            foreach ($this->arglist as $key => $arg) {
                $local_env[$arg] = $args[$key];
            }
            return $env->eval2($this->body, $local_env, false, 0);
        }
    }

}
