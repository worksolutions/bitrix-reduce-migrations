<?php

namespace WS\ReduceMigrations\Scenario;

/**
 * Class ScriptScenario
 *
 * @package WS\ReduceMigrations
 */
abstract class ScriptScenario {

    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_OPTIONAL = 'optional';

    const SHORTENED_HASH_LENGTH = 8;

    /**
     * @var array
     */
    private $data;

    public static function className() {
        return get_called_class();
    }

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
    public function setData(array $value = array()) {
        $this->data = $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setDataByKey($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getDataByKey($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
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
        return substr(static::hash(), 0 , self::SHORTENED_HASH_LENGTH);
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
        return self::priority() === self::PRIORITY_OPTIONAL;
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
    public static function name() {
        return null;
    }

    /**
     * @return string - is hash
     */
    public static function hash() {
        return null;
    }

    /**
     * @return int approximately time in seconds
     */
    public static function approximatelyTime() {
        return 0;
    }

    /**
     * Returns priority of migration
     *
     * @return string
     */
    public static function priority() {
        return self::PRIORITY_HIGH;
    }

}