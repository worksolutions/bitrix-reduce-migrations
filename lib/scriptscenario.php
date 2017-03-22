<?php

namespace WS\ReduceMigrations;

use WS\ReduceMigrations\Reference\ReferenceController;

/**
 * Class ScriptScenario
 * Base class for handle code scenarios
 *
 * @package WS\Migrations
 */
abstract class ScriptScenario
{

    /**
     * @var array
     */
    private $_data;

    /**
     * @var ReferenceController
     */
    private $_referenceController;

    /**
     * @param array $data
     * @param Reference\ReferenceController $controller
     */
    public function __construct(array $data = array(), ReferenceController $controller) {
        $this->setData($data);
        $this->_referenceController = $controller;
    }

    /**
     * @return Reference\ReferenceController
     */
    public function getReferenceController() {
        return $this->_referenceController;
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