<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of clojure
 *
 * @author snow
 */
require("lexer.php");
require("syntax.php");

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

class String extends Type {

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

class Clojure {

    protected $lexer;
    protected $syntax;
    protected $ast;
    protected $var_table = [];
    protected $symbol_table = [];

    function __construct($prog) {
        $this->lexer = new Lexer($prog);
        $this->syntax = new Syntax($this->lexer);
        $this->ast = $this->syntax->get_form();
        //var_dump($this->ast);
    }

    function getAst() {
        return $this->ast;
    }

    function toPHP($ast, &$symbol_table = [], $isQuote = false, $level = 0) {
        if (!$ast) {
            return "";
        }
        switch ($ast['type']) {
            case 'Form':
                return $this->toPHP($ast['left'], $symbol_table, $isQuote, $level + 1) . ';' . $this->toPHP($ast['right'], $symbol_table, $isQuote, $level + 1);
                break;
            case 'Double':
                return "new Double({$ast['val']})";
                break;
            case 'Ratio':
                return "new Ratio({$ast['val']})";
                break;
            case 'Integer':
                return "new Integer({$ast['val']})";
                break;
            case 'String':
                return "new String(\"" . (addcslashes($ast['val'])) . "\")";
                break;
            case 'Quote':
                return $this->toPHP($ast['left'], $symbol_table, true, $level + 1);
                break;
            case 'Character':
                return "new Character({$ast['val']})";
                break;
            case 'Regex':
                return "new Regex({$ast['val']})";
                break;
            case 'Boolean':
                return "new Boolean({$ast['val']})";
                break;
            case 'List':
                if ($isQuote) {
                    $ret = "new CList([";
                    $list = $ast;
                    while ($list['right']) {
                        $ret .= $this->toPHP($list['left'], $symbol_table, $isQuote, $level + 1);
                        $list = $list['right'];
                    }
                    return $ret . "])";
                } else {
                    switch ($ast['left']['val']) {
                        case '+':
                            return $this->add($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                        case '-':
                            return $this->sub($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                        case '*':
                            return $this->mul($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                        case '/':
                            return $this->div($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                        case '%':
                            return $this->mod($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                        case 'defn':
                            return $this->defn($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                        case 'def':
                            return $this->def($ast['right'], $symbol_table, $isQuote, $level);
                            break;
                    }
                    $func = $this->toPHP($ast['left'], $symbol_table, $isQuote, $level + 1);
                    $args = [];
                    $arg = $ast['right'];
                    while ($arg['right']) {
                        $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
                        $arg = $arg['right'];
                    }
                    return sprintf("%s(%s)", $func, implode(",", $args));
                }
                break;
            case 'Nil':
                return "NULL";
                break;
            case 'Map':
                break;
            case 'Set':
                break;
            case 'Vector':
                break;
            case 'Atom':
                if ($isQuote) {
                    return "\"{$ast['val']}\"";
                } else {

                    if ($key = array_search($ast['val'], $symbol_table)) {
                        return "{$key}";
                    } else {
                        //new symbol
                        $key = "\$sym_{$level}_" . sizeof($symbol_table) . "_" . rand(1, 100);
                        $symbol_table[$key] = $ast['val'];
                        return "{$key}";
                    }
                }
                break;
        }
    }

    function add(&$ast, &$symbol_table, $isQuote, $level) {
        $args = [];
        $arg = $ast;
        while ($arg['right']) {
            $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
            $arg = $arg['right'];
        }
        return sprintf("(%s)", implode("+", $args));
    }

    function sub(&$ast, &$symbol_table, $isQuote, $level) {
        $args = [];
        $arg = $ast;
        while ($arg['right']) {
            $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
            $arg = $arg['right'];
        }
        return sprintf("(%s)", implode("-", $args));
    }

    function mul(&$ast, &$symbol_table, $isQuote, $level) {
        $args = [];
        $arg = $ast;
        while ($arg['right']) {
            $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
            $arg = $arg['right'];
        }
        return sprintf("(%s)", implode("*", $args));
    }

    function div(&$ast, &$symbol_table, $isQuote, $level) {
        $args = [];
        $arg = $ast;
        while ($arg['right']) {
            $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
            $arg = $arg['right'];
        }
        return sprintf("(%s)", implode("/", $args));
    }

    function mod(&$ast, &$symbol_table, $isQuote, $level) {
        $args = [];
        $arg = $ast;
        while ($arg['right']) {
            $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
            $arg = $arg['right'];
        }
        return sprintf("(%s)", implode("%", $args));
    }

    function defn(&$ast, &$symbol_table, $isQuote, $level) {
        $func = $this->toPHP($ast['left'], $symbol_table, $isQuote, $level + 1);
        $args = [];
        $arg = $ast['right']['left'];
        while ($arg['right']) {
            $args [] = $this->toPHP($arg['left'], $symbol_table, $isQuote, $level + 1);
            $arg = $arg['right'];
        }
        $body = $this->toPHP($ast['right']['right']['left'], $symbol_table, $isQuote, $level + 1);
        return sprintf("function %s(%s){%s}", $func, implode(',', $args), $body);
    }

    function def(&$ast, &$symbol_table, $isQuote, $level) {
        $func = $this->toPHP($ast['left'], $symbol_table, $isQuote, $level + 1);
        $body = $this->toPHP($ast['right']['left'], $symbol_table, $isQuote, $level + 1);
        return sprintf(" (%s = %s)",$func,$body);
    }

    function eval1($ast) {
        
    }

}

$obj = new Clojure("(defn add1 [x] (+ (* x x) 1))");
echo $obj->toPHP($obj->getAst());
