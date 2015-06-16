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

    function toPHP($ast,$symbol_table=[],$isQuote=false){
    	if(!$ast){return "";}
        switch($ast['type']){
            case 'Form':
            return $this->toPHP($ast['left'],$symbol_table,$isQuote) .  $this->toPHP($ast['right'],$symbol_table,$isQuote);
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
            return "new String(\"" .(addcslashes($ast['val'])). "\")";
                break;
            case 'Quote':
            return $this->toPHP($ast['left'],$symbol_table,true);
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
            return "new List([])";
                break;
            case 'Nil':
            return "";
                break;
            case 'Map':
                break;
            case 'Set':
                break;
            case 'Vector':
                break;
            case 'Atom':
            if($isQuote){
                return "\"{$ast['val']}\"";
            }else{

            }
                break;
            }
            }
        }
    }
    
    function eval1($ast) {
        
    }

}

$obj = new Clojure("(defn square-corners [bottom left size]
         (let [top (+ bottom size)
               right (+ left size)]
           [[bottom left] [top left] [top right] [bottom right]]))");
