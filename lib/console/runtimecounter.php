<?php

namespace WS\ReduceMigrations\Console;

class RuntimeCounter {
    public $migrationNumber = 0;
    public $migrationCount = 0;
    public $start;
    public function __construct() {
        $this->start = microtime(true);
        $this->migrationCount = 0;
        $this->migrationNumbe = 0;
    }
}