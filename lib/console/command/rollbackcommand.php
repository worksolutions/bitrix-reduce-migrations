<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;

class RollbackCommand extends BaseCommand {

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
        $this->module
            ->rollbackLastChanges($callback);

        $interval = round(microtime(true) - $start, 2);

        $this->console
            ->printLine("Rollback action finished! Time $interval sec", Console::OUTPUT_PROGRESS);
    }
}
