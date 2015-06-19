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
require("clj/lang.php");

class Interpreter {

    protected $lexer;
    protected $syntax;
    protected $ast;
    protected $var_table = [];
    protected $global_symbol_table = [];

    function __construct($prog) {
        $this->lexer = new Lexer($prog);
        $this->syntax = new Syntax($this->lexer);
        $this->ast = $this->syntax->get_form();
    }

    function getAst() {
        return $this->ast;
    }

    function eval2($ast, $symbol_table = [], $isQuote = false, $level = 0) {
        if (!$ast) {
            return NULL;
        }
        switch ($ast['type']) {
            case 'Form':
                if (empty($ast['right'])) {
                    return $this->eval2($ast['left'], $symbol_table, $isQuote, $level + 1);
                } else {
                    $this->eval2($ast['left'], $symbol_table, $isQuote, $level + 1);
                    return $this->eval2($ast['right'], $symbol_table, $isQuote, $level + 1);
                }
                break;
            case 'Double':
                return (new clojure\Double($ast['val']));
                break;
            case 'Ratio':
                return new clojure\Ratio($ast['val']);
                break;
            case 'Integer':
                return new clojure\Integer($ast['val']);
                break;
            case 'String':
                return new clojure\CString($ast['val']);
                break;
            case 'Quote':
                return $this->eval2($ast['left'], $symbol_table, true, $level + 1);
                break;
            case 'Character':
                return new clojure\Character($ast['val']);
                break;
            case 'Regex':
                return new clojure\Regex($ast['val']);
                break;
            case 'Boolean':
                return new clojure\Boolean($ast['val']);
                break;
            case 'List':
                if ($isQuote) {
                    $ret = [];
                    $list = $ast;
                    while ($list['right']) {
                        $ret [] = $this->eval2($list['left'], $symbol_table, $isQuote, $level + 1);
                        $list = $list['right'];
                    }
                    return new clojure\CList($ret);
                } else {
                    if ($ast['left']['type'] == 'Atom') {
                        switch ($ast['left']['val']) {
                            case 'fn':
                                return $this->fn($ast['right'], $symbol_table, $isQuote, $level);
                                break;
                            case 'def':
                                return $this->def($ast['right'], $symbol_table, $isQuote, $level);
                                break;
                            case 'if':
                                return $this->cond($ast['right'], $symbol_table, $isQuote, $level);
                                break;
                        }
                    }
                    $func = $this->eval2($ast['left'], $symbol_table, $isQuote, $level);
                    $args = [];
                    $arg = $ast['right'];
                    while ($arg['right']) {
                        $args [] = $this->eval2($arg['left'], $symbol_table, $isQuote, $level + 1);
                        $arg = $arg['right'];
                    }
                    return $this->call($func, $args, $symbol_table);
                }
                break;
            case 'Nil':
                return new clojure\Nil(NULL);
                break;
            case 'Map':
                break;
            case 'Set':
                break;
            case 'Vector':
                break;
            case 'Atom':
                if ($isQuote) {
                    return $ast['val'];
                } else {
                    return $this->getVal($ast, $symbol_table);
                }
                break;
        }
    }

    function call($func, $args, $symbol_table) {
        return $func->call($args, $this, $symbol_table);
    }

    function getVal($ast, &$symbol_table, $isGlobal = false) {
        if (!empty($symbol_table[$ast['val']])) {
            return $symbol_table[$ast['val']];
        } else {
            if (!empty($this->global_symbol_table[$ast['val']])) {
                return $this->global_symbol_table[$ast['val']];
            }
        }
        return NULL;
    }

    function mkFunc($name, $func) {
        $func = new clojure\Func('native', NULL, $func);
        $this->global_symbol_table[$name] = $func;
        return $func;
    }

    function mkSymName($name, $symbol_table, $level) {
        return "{$name}_{$level}_" . sizeof($symbol_table) . "_" . rand(0, 100);
    }

    function fn(&$ast, &$symbol_table, $isQuote, $level) {
//        $func = $this->mkSymName('closure', $symbol_table, $level);
        $args = [];
        $arg = $ast['left'];
        while ($arg['right']) {
            $args [] = $arg['left']['val'];
            $arg = $arg['right'];
        }
//        $symbol_table[$func] = new clojure\Lambda($args,$ast['right']['right']['left']);
//        return $symbol_table[$func];
        return new clojure\Lambda($args, $ast['right']['left']);
        }
        function cond(&$ast, &$symbol_table, $isQuote, $level) {
        $cond = $this->eval2($ast['left'], $symbol_table, $isQuote, $level);
        if($cond->get_value()){
            return $this->eval2($ast['right']['left'], $symbol_table, $isQuote, $level + 1);
        }else if(!empty($ast['right']['right'])){
            return $this->eval2($ast['right']['right']['left'], $symbol_table, $isQuote, $level + 1);
        }
    }

    function def(&$ast, &$symbol_table, $isQuote, $level) {
        $this->global_symbol_table[$ast['left']['val']] = $this->eval2($ast['right']['left'], $symbol_table, $isQuote, $level + 1);
        return $this->global_symbol_table[$ast['left']['val']];
    }

}

$obj = new Interpreter("(class 1)");
$obj->mkFunc('+', function($args, $env, $local_env) {
    $ret = 0;
    foreach ($args as $arg) {
        switch (get_class($arg)) {
            case 'clojure\\Integer':
                $int = $arg->get_value();
                if (is_array($ret)) {
                    $ret[0] = $int * $ret[1] + $ret[0];
                } else {
                    $ret += $int;
                }
                break;
            case 'clojure\\Double':
                $double = $arg->get_value();
                if (is_array($ret)) {
                    $ret = $double + $ret[0] / $ret[1];
                } else {
                    $ret += $double;
                }
                break;
            case 'clojure\\Ratio':
                $ratio = $arg->get_value();
                if (is_integer($ret)) {
                    $ratio[0] = $ratio[0] + $ratio[1] * $ret;
                    $ret = $ratio;
                } else if (is_array($ret)) {
                    $ret[0] = $ratio[0] * $ret[1] + $ratio[1] * $ret[0];
                    $ret[1] = $ret[1] * $ratio[1];
                } else {
                    $ret += $ratio[0] / $ratio[1];
                }
                break;
            default://类型错误
                break;
        }
    }
    if (is_integer($ret)) {
        return new clojure\Integer($ret);
    } elseif (is_float($ret)) {
        return new clojure\Double($ret);
    } else {
        return new clojure\Ratio($ret);
    }
});

$obj->mkFunc('/', function($args, $env, $local_env) {
    $tmp = null;
    foreach ($args as $arg) {
        if (empty($tmp)) {
            $tmp = $arg;
        } else {
            $tmp = $tmp->div($arg);
        }
    }
    return $tmp;
});
$obj->mkFunc('-', function($args, $env, $local_env) {
    $tmp = null;
    foreach ($args as $arg) {
        if (empty($tmp)) {
            $tmp = $arg;
        } else {
            $tmp = $tmp->sub($arg);
        }
    }
    return $tmp;
});
$obj->mkFunc('*', function($args, $env, $local_env) {
    $tmp = null;
    foreach ($args as $arg) {
        if (empty($tmp)) {
            $tmp = $arg;
        } else {
            $tmp = $tmp->mul($arg);
        }
    }
    return $tmp;
});
$obj->mkFunc('=', function($args, $env, $local_env) {
    return new clojure\Boolean($args[0]->get_value() == $args[1]->get_value());
});
$obj->mkFunc('>', function($args, $env, $local_env) {
    return new clojure\Boolean($args[0]->get_value() > $args[1]->get_value());
});
$obj->mkFunc('<', function($args, $env, $local_env) {
    return new clojure\Boolean($args[0]->get_value() < $args[1]->get_value());
});
$obj->mkFunc('class', function($args, $env, $local_env) {
    $ret = 0;
    if (sizeof($args) == 1) {
        return new clojure\Atom(get_class($args[0]));
    } else {
        
    }
});
var_Dump($obj->eval2($obj->getAst()));
