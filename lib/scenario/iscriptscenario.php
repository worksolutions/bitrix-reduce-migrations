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
    static public function name();

    /**
     * @return string - is hash
     */
     public static function hash();

    /**
     * @return string - is owner name
     */
    public static function owner();

    /**
     * Returns priority of migration
     *
     * @return string
     */
    static public function priority();

}