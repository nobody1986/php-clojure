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
class Lexer {

    protected $token = [];
    protected $code;
    protected $symbols = ['(', ')', '#', '[', ']', "'", '\\', '{', '}'];
    protected $spaces = [' ', "\n", "\t", "\r"];
    protected $position = 0;
    protected $token_size = 0;

    function __construct($code) {
        $this->code = $code;
        $this->parse();
        $this->token_size = sizeof($this->token);
    }

    function get_next_token() {
        if ($this->position < $this->token_size) {
            return $this->token[$this->position++];
        }
        return NULL;
    }

    function get_prev_token() {
        if ($this->position == 0) {
            return NULL;
        }
        --$this->position;
        if ($this->position < $this->token_size) {
            return $this->token[$this->position];
        }
        return NULL;
    }

    function reseek() {
        $this->position = 0;
    }

    function get_token() {
        return $this->token;
    }

    function is_digit($c) {
        return $c >= '0' && $c <= '9';
    }

    function is_space($c) {
        return in_array($c, $this->spaces);
    }

    function parse_number($index, $len) {
        $tok = array();
        $position = 0;
        if ($this->code[$index] == '+' || $this->code[$index] == '-') {
            $cur = $index + 1;
            if ($index == $len || !$this->is_digit($this->code[$cur])) {
                $tok['type'] = 'atom';
                $tok['tok'] = $this->code[$index];
                $tok['cur'] = $index;
                return $tok;
            }
        } else {
            $cur = $index;
        }
        $float = false;
        for ($i = $cur; $i < $len; ++$i) {
            if (!$this->is_digit($this->code[$i]) && !(!$float && $this->code[$i] == '.')) {
                $position = $i - 1;
                break;
            }
            if ($this->code[$i] == '.') {
                $float = true;
            }
        }
        if ($this->code[$position + 1] == 'e' || $this->code[$position + 1] == 'E') {
            if ($this->code[$position + 2] == '+' || $this->code[$position + 2] == '-') {
                $cur = $index + 3;
            } else {
                $cur = $position + 2;
            }
            $float = false;
            for ($i = $cur; $i < $len; ++$i) {
                if (!$this->is_digit($this->code[$i]) && !(!$float && $this->code[$i] == '.')) {
                    $position = $i - 1;
                    break;
                }
                if ($this->code[$i] == '.') {
                    $float = true;
                }
            }
        }
        $tok['type'] = 'number';
        $tok['is_float'] = $float;
        $tok['tok'] = substr($this->code, $index, $i - $index);
        $tok['cur'] = $position;
        return $tok;
    }

    function parse_string($index, $len) {
        $tok = array();
        $backslash = false;
        for ($i = $index + 1; $i < $len; ++$i) {
            if (!$backslash && $this->code[$i] == '"') {
                break;
            }
            if ($this->code[$i] == '\\') {
                $backslash = true;
                continue;
            }
            $backslash = false;
        }
        $tok['type'] = 'string';
        $tok['tok'] = stripslashes(substr($this->code, $index + 1, $i - $index - 1));
        $tok['cur'] = $i;
        return $tok;
    }

    function parse_atom($index, $len) {
        $tok = array();
        $position = 0;
        for ($i = $index; $i < $len; ++$i) {
            if ($this->is_space($this->code[$i])) {
                $position = $i;
                break;
            }
            if (in_array($this->code[$i], $this->symbols)) {
                $position = $i - 1;
                break;
            }
        }
        $tok['type'] = 'atom';
        $tok['tok'] = substr($this->code, $index, $i - $index);
        $tok['cur'] = $position;
        return $tok;
    }

    function add_token($token, $type) {
        array_push($this->token, ['tok' => $token, 'type' => $type]);
    }

    function parse() {
        $len = strlen($this->code);
        for ($i = 0; $i < $len; ++$i) {
            if ($this->is_space($this->code[$i])) {
                continue;
            }
            if (in_array($this->code[$i], $this->symbols)) {
                $this->add_token($this->code[$i], $this->code[$i]);
            } elseif ($this->is_digit($this->code[$i]) || $this->code[$i] == '+' || $this->code[$i] == '-') {
                $parse = $this->parse_number($i, $len);
                $this->add_token($parse['tok'], $parse['type']);
                $i = $parse['cur'];
            } elseif ($this->code[$i] == '"') {
                $parse = $this->parse_string($i, $len);
                $this->add_token($parse['tok'], $parse['type']);
                $i = $parse['cur'];
            } else {
                $parse = $this->parse_atom($i, $len);
                $this->add_token($parse['tok'], $parse['type']);
                $i = $parse['cur'];
            }
        }
    }

}


