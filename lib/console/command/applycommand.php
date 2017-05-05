<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;
use WS\ReduceMigrations\Timer;

class ApplyCommand extends BaseCommand{
    const FLAG_FORCE = '-f';
    const FLAG_SKIP_OPTIONAL = '--skip-optional';

    /** @var  bool */
    private $force;
    /** @var  bool */
    private $skipOptional;
    /** @var  string */
    private $migrationHash;

    protected function initParams($params) {
        $this->force = isset($params[self::FLAG_FORCE]) ? $params[self::FLAG_FORCE] : false;
        $this->skipOptional = isset($params[self::FLAG_SKIP_OPTIONAL]) ? $params[self::FLAG_SKIP_OPTIONAL] : false;
        $this->migrationHash = isset($params[0]) ? $params[0] : null;

    }

    public function execute($callback = false) {
        $this->confirmAction();

        $this->console
            ->printLine('Applying new migrations started....', Console::OUTPUT_PROGRESS);

        $timer = new Timer();
        $timer->start();
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


        $timer->stop();
        $time = $this->console->formatTime($timer->getTime());
        $this->console
            ->printLine("Apply action finished! $count items, time {$time}", Console::OUTPUT_PROGRESS);
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
            ->printLine(sprintf('Migrations for apply - %s, approximate time - %s', $count, $this->console->formatTime($time)))
            ->printLine('Are you sure? (yes|no):');

        $answer = $this->console->readLine();

        if ($answer !== self::CONFIRM_WORD) {
            exit();
        }
    }

}
