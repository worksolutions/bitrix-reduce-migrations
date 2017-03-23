<?php

namespace WS\ReduceMigrations;

/**
 * Class ScriptScenario
 *
 * @package WS\ReduceMigrations
 */
abstract class ScriptScenario implements IScriptScenario {

    /**
     * @var array
     */
    private $_data;

    /**
     * @param array $data
     */
    public function __construct(array $data = array()) {
        $this->setData($data);
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * @param array $value
     */
    protected function setData(array $value = array()) {
        $this->_data = $value;
    }

    /**
     * Check to valid class definition
     *
     * @return bool
     */
    static public function isValid() {
        return static::name();
    }
}