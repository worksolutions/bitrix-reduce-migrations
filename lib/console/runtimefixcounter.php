<?php

namespace WS\ReduceMigrations\Console;

use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Module;

class RuntimeFixCounter {

    public $time;
    public $activeFixName;
    public $fixNames;
    public $fixNumber;

    public function __construct() {
        $this->time = 0;
        $this->activeFixName = '';
        $this->fixNames = array();
        $this->fixNumber = 0;
        $this->migrationCount = 0;
    }

    /**
     * @param AppliedChangesLogModel[] $list
     */
    public function setFixNamesByLogs($list) {
        foreach ($list as $log) {
            if ($log->isFailed()) {
                continue;
            }
            $processName = $log->description;
            if ($processName == Module::SPECIAL_PROCESS_SCENARIO) {
                $this->migrationCount++;
                continue;
            }
            if ($this->activeFixName != $log->description) {
                $this->migrationCount++;
                $this->fixNumber++;
                $this->activeFixName = $log->description;
            }
            $this->fixNames[$this->activeFixName . $this->fixNumber]++;
        }
    }

}