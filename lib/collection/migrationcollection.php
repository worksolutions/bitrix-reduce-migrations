<?php

namespace WS\ReduceMigrations\Collection;

use WS\ReduceMigrations\Scenario\ScriptScenario;

class MigrationCollection {
    /** @var ScriptScenario[]  */
    private $elements;

    public function __construct($files = array()) {
        foreach ($files as $file) {
            $fileClass = str_replace(".php", "", $file->getName());
            if (!class_exists($fileClass)) {
                include $file->getPath();
            }

            if (!is_subclass_of($fileClass, '\WS\ReduceMigrations\Scenario\ScriptScenario')) {
                continue;
            }
            if (!$fileClass::isValid()) {
                continue;
            }
            $this->elements[] = $fileClass;
        }
    }

    /**
     * @return float|int
     */
    public function getApproximateTime() {
        $time = 0;
        foreach ($this->elements as $element) {
            $time += (double)$element::approximatelyTime();
        }
        return $time;
    }

    /**
     * @return array
     */
    public function groupByPriority() {
        $elements = array();
        $priorities = ScriptScenario::getPriorities();
        foreach ($priorities as $priority) {
            $elements[$priority] = array();
        }
        $elements = array();
        foreach ($this->elements as $key => $element) {
            $elements[$element::priority()][$key] = $element;
        }

        return array_filter($elements);
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->elements;
    }

    /**
     * @param $migrationHash
     *
     * @return ScriptScenario[]
     */
    public function findByHash($migrationHash) {
        $list = array();
        foreach ($this->elements as $element) {
            if (strpos($element::hash(), $migrationHash) !== 0) {
                continue;
            }
            $list[] = $element;
        }
        return $list;
    }


}
