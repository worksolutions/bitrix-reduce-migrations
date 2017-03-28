<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;

class RollbackCommand extends BaseCommand {

    private $migrationHash;

    protected function initParams($params) {
        $this->migrationHash = isset($params[0]) ? $params[0] : null;
    }

    public function execute($callback = false) {
        $this->console
            ->printLine("Are you sure? (yes|no):");

        $answer = $this->console
            ->readLine();

        if ($answer != 'yes') {
            return;
        }
        $this->console
            ->printLine("Rollback action started...", Console::OUTPUT_PROGRESS);
        $start = microtime(true);
        try {
            if ($this->migrationHash) {
                $this->module->rollbackByHash($this->migrationHash);
            } else {
                $this->module
                    ->rollbackLastChanges($callback);
            }
        } catch (\Exception $e) {
            throw new ConsoleException($e->getMessage());
        }


        $interval = round(microtime(true) - $start, 2);

        $this->console
            ->printLine("Rollback action finished! Time $interval sec", Console::OUTPUT_PROGRESS);
    }
}
