<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;

class ApplyCommand extends BaseCommand{
    /** @var  bool */
    private $force;
    /** @var  bool */
    private $skipOptional;
    /** @var  string */
    private $migrationHash;

    protected function initParams($params) {
        $this->force = isset($params['-f']) ? $params['-f'] : false;
        $this->skipOptional = isset($params['--skip-optional']) ? $params['--skip-optional'] : false;
        $this->migrationHash = isset($params[0]) ? $params[0] : null;

    }

    public function execute($callback = false) {
        $this->confirmAction();

        $this->console
            ->printLine('Applying new migrations started....', Console::OUTPUT_PROGRESS);

        $time = microtime(true);

        try {
            if ($this->migrationHash) {
                $count = 1;
                $this->module
                    ->applyMigrationByHash($this->migrationHash, $callback);
            } else {
                $count = (int)$this->module
                    ->applyMigrations($this->skipOptional, $callback);
            }
        } catch (\Exception $e) {
            throw new ConsoleException($e->getMessage());
        }


        $interval = round(microtime(true) - $time, 2);

        $this->console
            ->printLine("Apply action finished! $count items, time $interval sec", Console::OUTPUT_PROGRESS);
    }

    private function confirmAction() {
        if ($this->force) {
            return true;
        }
        $notAppliedScenarios = $this->module->getNotAppliedScenarios();
        $count = count($notAppliedScenarios->toArray());
        $time = $notAppliedScenarios->getApproximateTime();
        if ($this->migrationHash) {
            $migrations = $notAppliedScenarios->findByHash($this->migrationHash);
            $count = count($migrations);
            $time = array_reduce($migrations, function ($result, $item) {
                return $result + (int)$item::approximatelyTime();
            }, 0);
        }
        $this->console
            ->printLine(sprintf('Migrations for apply - %s, approximate time - %s min', $count, $time))
            ->printLine('Are you sure? (yes|no):');

        $answer = $this->console->readLine();

        if ($answer != 'yes') {
            exit();
        }
    }

}
