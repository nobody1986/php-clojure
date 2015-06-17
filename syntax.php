<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *  number   integer double ratio
 *  string      ""
 *  symbol    '
 *  Keyword :
 *  Boolean  true/false
 *  Character   \x
 *  nil
 *  regex      #""
 *  list 
 *  vector     []
 *  map        {}
 *  set          #{}
 *
 * @author snow
 */
class Syntax {

    /**
     * @var Lexer
     */
    protected $lexer;
    protected $ast = [];

    function __construct($lexer) {
        $this->lexer = $lexer;
    }

    function get_form() {
        $ast = ['type' => 'form', 'left' => NULL, 'right' => NULL];
        while (($tok = $this->lexer->get_next_token()) != NULL) {
            $node = $this->get_expr($tok);
            return ['left' => $node, 'right' => $this->get_form(),'type' => 'Form'];
        }
        return NULL;
    }

    function get_expr($tok) {
        switch ($tok['type']) {
            case 'atom':
                $node = $this->get_atom($tok);
                break;
            case 'number':
                $node = $this->get_number($tok);
                break;
            case 'string':
                $node = $this->get_string($tok);
                break;
            default:
                $node = $this->get_syntax($tok);
                break;
        }
        return $node;
    }

    function get_number($tok) {
        if (!empty($tok['is_float'])) {
            return [
                'type' => 'Double',
                'val' => floatval($tok['tok'])
            ];
        } else {
            $tok1 = $this->lexer->get_next_token();
            if (!empty($tok) && $tok['type'] == '/') {
                $tok2 = $this->lexer->get_next_token();
                if (!($tok2['type'] == 'number' && !$tok2['is_float'])) {
                    //语法错误
                }
                return [
                    'type' => 'Ratio',
                    'val' => [intval($tok['tok']), intval($tok2['tok'])]
                ];
            } else {
                $this->lexer->get_prev_token();
                return [
                    'type' => 'Integer',
                    'val' => intval($tok['tok'])
                ];
            }
        }
    }

    function get_string($tok) {
        return [
            'type' => 'String',
            'val' => ($tok['tok'])
        ];
    }

    function get_syntax($tok) {
        switch ($tok['type']) {
            case '\''://quote
                return [
                    'type' => 'Quote',
                    'left' => $this->get_expr($this->lexer->get_next_token())
                ];
                break;
            case ':'://Keyword
                $tok1 = $this->lexer->get_next_token();
                if (!empty($tok1) && $tok1['type'] == 'atom') {
                    return [
                        'type' => 'Keyword',
                        'val' => ($tok['tok'])
                    ];
                } else {
                    //语法错误
                }
                break;
            case '\\'://Character
                $tok1 = $this->lexer->get_next_token();
                if ($tok1['type'] != 'atom') {
                    //语法错误
                }
                return [
                    'type' => 'Character',
                    'val' => ($tok['tok'])
                ];
                break;
            case '#'://Regex/Set
                $tok1 = $this->lexer->get_next_token();
                if ($tok1['type'] = 'string') {
                    return [
                        'type' => 'Regex',
                        'val' => ($tok['tok'])
                    ];
                } elseif ($tok1['type'] == '{') {
                    return $this->get_set($tok);
                } else {
                    //语法错误
                }
            case '['://Vector
                return $this->get_vector($tok);
                break;
            case '('://List
                return $this->get_list($tok);
                break;
            case '{'://Map
                return $this->get_map($tok);
                break;
        }
    }

    function get_atom($tok) {
        if ($tok['tok'] == 'true') {
            return [
                'type' => 'Boolean',
                'val' => True
            ];
        } else if ($tok['tok'] == 'false') {
            return [
                'type' => 'Boolean',
                'val' => False
            ];
        }else if ($tok['tok'] == 'nil') {
            return [
                'type' => 'Nil',
            ];
        }else{
            return [
                'type' => 'Atom',
                'val'   => $tok['tok']
            ];
        }
    }

    function get_list($tok) {
        $ret = [
            'type' => 'List',
            'left' => NULL,
            'right' => NULL,
        ];
        $tmp = &$ret;
        while (($tok = $this->lexer->get_next_token()) != NULL && $tok['type'] != ')') {
            $tmp['left'] = $this->get_expr($tok);
            $tmp['right'] = [
                'type' => 'List',
                'left' => NULL,
                'right' => NULL,
            ];
            $tmp = &$tmp['right'];
        }
        return $ret;
    }

    function get_vector($tok) {
        $ret = [
            'type' => 'Vector',
            'left' => NULL,
            'right' => NULL,
        ];
        $tmp = &$ret;
        while (($tok = $this->lexer->get_next_token()) != NULL && $tok['type'] != ']') {
            $tmp['left'] = $this->get_expr($tok);
            $tmp['right'] = [
                'type' => 'Vector',
                'left' => NULL,
                'right' => NULL,
            ];
            $tmp = &$tmp['right'];
        }
        return $ret;
    }

    function get_map($tok) {
        $ret = [
            'type' => 'Map',
            'left' => NULL,
            'right' => NULL,
            'val' => NULL,
        ];
        $tmp = &$ret;
        while (($tok = $this->lexer->get_next_token()) != NULL && $tok['type'] != ']') {
            $tmp['left'] = $this->get_expr($tok);
            $tmp['val'] = $this->get_expr($tok);
            $tmp['right'] = [
                'type' => 'Map',
                'left' => NULL,
                'right' => NULL,
                'val' => NULL,
            ];
            $tmp = &$tmp['right'];
        }
        return $ret;
    }

    function get_set($tok) {
        $ret = [
            'type' => 'Set',
            'left' => NULL,
            'right' => NULL,
        ];
        $tmp = &$ret;
        while (($tok = $this->lexer->get_next_token()) != NULL && $tok['type'] != '}') {
            $tmp['left'] = $this->get_expr($tok);
            $tmp['right'] = [
                'type' => 'Set',
                'left' => NULL,
                'right' => NULL,
            ];
            $tmp = &$tmp['right'];
        }
        return $ret;
    }

}
