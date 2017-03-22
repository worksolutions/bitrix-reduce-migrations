<?php

namespace WS\ReduceMigrations;

/**
 * Класс объекты которого являются специализированные callback функции.
 * Работает только начиная с версии php 5.3
 * @author Максим Соколовский (my.sokolovsky@gmail.com)
 */
class Callback {

    private $_function;
    private static $_count = 0;
    private $_number;
    private $_args  = array();
    private $_callByToString = false;

    /**
     * @param callback $f
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed ...
     */
    public function __construct() {
        $args = func_get_args();
        $f    = array_shift($args);
        if ( ! is_callable($f)) {
            throw new \Exception("Parametr is not function");
        }
        $this->_function = $f;
        if (isset($args) && is_array($args)) {
            call_user_func_array(array($this, 'bind'), $args);
        }
        $this->_number = self::$_count ++;
    }

    public function bind() {
        if ( ! empty($this->_args)) {
            throw new \Exception("Args setup before");
        }
        $this->_args = func_get_args();
    }

    /**
     * Параметры для вызова
     *
     * @param mixed $param1
     * @param mixed $param1
     *
     * @return mixed
     */
    public function __invoke() {
        $args = array_merge($this->_args, func_get_args() ? : array());
        return call_user_func_array($this->_function, $args);
    }

    public function setCallByToString($bool) {
        $this->_callByToString = (bool) $bool;
    }

    public function __toString() {
        if ($this->_callByToString) {
            return $this();
        }
        return 'CallBackObject_' . $this->_number;
    }

}
