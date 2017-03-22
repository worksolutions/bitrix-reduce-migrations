<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Module;

class ApplyCommand extends BaseCommand{
    /** @var  bool */
    private $force;

    protected function initParams($params) {
        $this->force = in_array('-f', $params);
    }

    public function execute($callback = false) {
        if (!$this->force) {
            $this->console
                ->printLine("Are you sure? (yes|no):");
            $answer = $this->console->readLine();
            if ($answer != 'yes') {
                exit();
            }
        }
        $diagnosticTester = $this->module
            ->useDiagnostic();

        if (!$diagnosticTester->run()) {
            $this->console
                ->printLine("Diagnostic is not valid");
            exit();
        }
        $this->console
            ->printLine("Applying new fixes started....", Console::OUTPUT_PROGRESS);

        $time = microtime(true);

        $count = (int)$this->module
            ->applyNewFixes($callback);

        $interval = round(microtime(true) - $time, 2);

        $this->console
            ->printLine("Apply action finished! $count items, time $interval sec", Console::OUTPUT_PROGRESS);
    }

}
