<?php

namespace WS\ReduceMigrations;

/**
 * Class ScriptScenario
 *
 * @package WS\ReduceMigrations
 */
abstract class ScriptScenario
{

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
     * Runs to commit migration
     */
    abstract public function commit();

    /**
     * Runs by rollback migration
     */
    abstract public function rollback();

    /**
     * Returns name of migration
     *
     * @return string
     */
    abstract static public function name();

    /**
     * @return array First element is hash, second is owner name
     */
    abstract public function version();

    /**
     * Returns description of migration
     *
     * @return string
     */
    abstract static public function description();

    /**
     * Check to valid class definition
     *
     * @return bool
     */
    static public function isValid() {
        return static::name();
    }
}