<?php

namespace WS\ReduceMigrations;

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
     * @return array First element is hash, second is owner name
     */
     public function version();

    /**
     * Returns description of migration
     *
     * @return string
     */
    static public function description();

}