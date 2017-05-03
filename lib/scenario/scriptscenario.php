<?php

namespace WS\ReduceMigrations\Scenario;

/**
 * Class ScriptScenario
 *
 * @package WS\ReduceMigrations
 */
abstract class ScriptScenario implements IScriptScenario {

    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_OPTIONAL = 'optional';

    const SHORTENED_HASH_LENGTH = 8;

    /**
     * @var array
     */
    private $data;

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
        return $this->data;
    }

    /**
     * @param array $value
     */
    protected function setData(array $value = array()) {
        $this->data = $value;
    }

    /**
     * Check to valid class definition
     *
     * @return bool
     */
    static public function isValid() {
        return static::name();
    }

    static public function getShortenedHash() {
        return substr(self::hash(), 0 , self::SHORTENED_HASH_LENGTH);
    }
    /**
     * @return array
     */
    public static function getPriorities() {
        return array(
            self::PRIORITY_HIGH,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_OPTIONAL,
        );
    }

    /**
     * @return bool
     */
    public function isOptional() {
        return $this->priority() === self::PRIORITY_OPTIONAL;
    }

}