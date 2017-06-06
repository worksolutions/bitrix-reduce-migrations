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
        $listCommand = new ListCommand($this->console, array($this->migrationHash));
        $listCommand->execute();

        $notAppliedScenarios  = $this->module->getNotAppliedScenarios();
        $count = $notAppliedScenarios->count();

        if ($count == 0) {
            return;
        }
        if ($this->migrationHash) {
            $hasByHash = false;
            foreach ($notAppliedScenarios->toArray() as $notAppliedScenario) {
                if (strpos($notAppliedScenario::getShortenedHash(), $this->migrationHash) !== false) {
                    $hasByHash = true;
                    break;
                }
            }
            if (!$hasByHash) {
                return;
            }
        }

        $this->confirmAction();

        $this->console
            ->printLine("\nApplying new migrations started...\n", Console::OUTPUT_PROGRESS);

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

        $this->console
            ->printLine('Are you sure? (yes|no):');

        $answer = $this->console->readLine();

        if ($answer !== self::CONFIRM_WORD) {
            exit();
        }
    }

}
