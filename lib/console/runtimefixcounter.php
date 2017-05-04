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
            if ($log->isFailed() || $log->isSkipped()) {
                continue;
            }
            $this->migrationCount++;
            continue;
        }
    }

}