<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;

class RollbackCommand extends BaseCommand {
    const TYPE_HASH = 0;
    const TYPE_COUNT = 1;
    const TYPE_TO_HASH = 2;
    const TYPE_LAST_BATCH = 3;

    private $migrationHash;
    private $count;
    private $toHash;
    private $type;

    protected function initParams($params) {
        $this->migrationHash = isset($params[0]) ? $params[0] : null;
        $this->count = isset($params['--count']) ? (int)$params['--count'] : null;
        if ($this->count && $this->count < 0) {
            $this->count = 0;
        }
        $this->toHash = isset($params['--to-hash']) ? $params['--to-hash'] : null;
        $this->type = $this->identifyType();
    }

    private function identifyType() {
        if ($this->migrationHash) {
            return self::TYPE_HASH;
        }
        if ($this->count) {
            return self::TYPE_COUNT;
        }
        if ($this->toHash) {
            return self::TYPE_TO_HASH;
        }

        return self::TYPE_LAST_BATCH;
    }

    public function execute($callback = false) {
        $start = microtime(true);
        try {
            $this->rollback($callback);
        } catch (\Exception $e) {
            throw new ConsoleException($e->getMessage());
        }

        $interval = round(microtime(true) - $start, 2);

        $this->console
            ->printLine("Rollback action finished! Time $interval sec", Console::OUTPUT_PROGRESS);
    }

    private function rollback($callback = false) {
        switch ($this->type) {
            case self::TYPE_HASH:
                $this->confirm("Rollback migration with hash={$this->migrationHash}.");
                $this->module->rollbackByHash($this->migrationHash);
                break;
            case self::TYPE_COUNT:
                $this->confirm("Rollback last {$this->count} migrations.");
                $this->module->rollbackLastFewMigrations($this->count, $callback);
                break;
            case self::TYPE_TO_HASH:
                $this->confirm("Rollback migrations to hash={$this->toHash}.");
                $this->module->rollbackToHash($this->toHash, $callback);
                break;
            case self::TYPE_LAST_BATCH:
                $this->confirm("Rollback last batch.");
                $this->module->rollbackLastBatch($callback);
                break;
        }

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
