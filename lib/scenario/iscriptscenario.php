<?php

namespace WS\ReduceMigrations\Scenario;

interface IScriptScenario {

    /**
     * Runs to commit migration
     */
    public function commit();

    /**
     * Runs by rollback migration
     */
    public function rollback();

    /**
     * Returns name of migration
     *
     * @return string
     */
    public static function name();

    /**
    * @return string - is hash
    */
    public static function hash();

    /**
     * @return string - is owner name
     */
    public static function owner();

    /**
     * @return string approximately time
     */
    public static function approximatelyTime();

    /**
     * Returns priority of migration
     *
     * @return string
     */
    public static function priority();

}