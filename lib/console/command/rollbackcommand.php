<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;

class RollbackCommand extends BaseCommand {

    private $migrationHash;
    private $count;
    private $toHash;

    protected function initParams($params) {
        $this->migrationHash = isset($params[0]) ? $params[0] : null;
        $this->count = isset($params['--count']) ? (int)$params['--count'] : null;
        if ($this->count && $this->count < 0) {
            $this->count = 0;
        }
        $this->toHash = isset($params['--to-hash']) ? $params['--to-hash'] : null;
    }

    public function execute($callback = false) {
        $start = microtime(true);
        try {
            if ($this->migrationHash) {
                $this->confirm("Rollback migration with hash={$this->migrationHash}.");
                $this->module->rollbackByHash($this->migrationHash);
            } elseif ($this->count) {
                $this->confirm("Rollback last {$this->count} migrations.");
                $this->module->rollbackLastFewMigrations($this->count, $callback);
            } elseif ($this->toHash) {
                $this->confirm("Rollback migrations to hash={$this->toHash}.");
                $this->module->rollbackToHash($this->toHash, $callback);
            } else {
                $this->confirm("Rollback last batch.");
                $this->module->rollbackLastBatch($callback);
            }
        } catch (\Exception $e) {
            throw new ConsoleException($e->getMessage());
        }

        $interval = round(microtime(true) - $start, 2);

        $this->console
            ->printLine("Rollback action finished! Time $interval sec", Console::OUTPUT_PROGRESS);
    }

    private function confirm($message) {
        $this->console
            ->printLine($message . " Are you sure? (yes|no):");

        $answer = $this->console
            ->readLine();

        if ($answer != 'yes') {
            throw new ConsoleException('Operation cancelled');
        }

        $this->console
            ->printLine("Rollback action started...", Console::OUTPUT_PROGRESS);
    }
}
