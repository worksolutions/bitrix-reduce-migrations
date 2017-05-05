<?php

/**
 * Class definition update migrations scenario actions
 **/
class #class_name# extends \WS\ReduceMigrations\Scenario\ScriptScenario {

    /**
     * Name of scenario
     **/
    static public function name() {
        return "#name#";
    }

    /**
     * Priority of scenario
     **/
    static public function priority() {
        return #priority#;
    }

    /**
     * @return string hash
     */
    static public function hash() {
        return "#hash#";
    }

    /**
     * @return int approximately time in seconds
     */
    static public function approximatelyTime() {
        return #time#;
    }

    /**
     * Write action by apply scenario. Use method `setData` for save need rollback data
     **/
    public function commit() {
        // my code
    }

    /**
     * Write action by rollback scenario. Use method `getData` for getting commit saved data
     **/
    public function rollback() {
        // my code
    }
}