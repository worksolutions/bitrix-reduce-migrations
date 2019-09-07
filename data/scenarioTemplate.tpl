<?php

/**
 * Class definition update migrations scenario actions
 **/
class #class_name# extends \WS\ReduceMigrations\Scenario\ScriptScenario {

    /**
     * Scenario title
     **/
    public static function name() {
        return '#name#';
    }

    /**
     * Priority of scenario
     **/
    public static function priority() {
        return #priority#;
    }

    /**
     * @return string hash
     */
    public static function hash() {
        return '#hash#';
    }

    /**
     * @return int approximately time in seconds
     */
    public static function approximatelyTime() {
        return #time#;
    }

    /**
     * Writes action by apply scenario. Use method `setData` to save needed rollback data.
     * For printing info into console use object from $this->printer() method.
     **/
    public function commit() {
        // my code
    }

    /**
     * Write action by rollback scenario. Use method `getData` for getting commit saved data.
     * For printing info into console use object from $this->printer() method.
     **/
    public function rollback() {
        // my code
    }
}
