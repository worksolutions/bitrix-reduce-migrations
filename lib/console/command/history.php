<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;
use WS\ReduceMigrations\Console\Formatter\Table;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\SetupLogModel;

class History extends BaseCommand {

    private $count;

    protected function initParams($params) {
        $this->count = isset($params[0]) ? $params[0] : false;
    }

    public function execute($callback = false) {
        $lastSetupLog = \WS\ReduceMigrations\Module::getInstance()->getLastSetupLog();
        if (!$lastSetupLog) {
            throw new ConsoleException('Nothing to show');
        }

        if (!$this->count) {
            $this->showLastBatch($lastSetupLog);
        } else {
            $this->showLastFewMigrations($this->count);
        }

    }

    /**
     * @param $count
     */
    private function showLastFewMigrations($count) {
        $this->console->printLine("Last {$count} applied migrations");
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'limit' => $count
        ));
        $this->show($logs);
    }

    /**
     * @param SetupLogModel $lastSetupLog
     */
    private function showLastBatch($lastSetupLog) {
        $this->console->printLine('Last applied migrations:');
        $this->show($lastSetupLog->getAppliedLogs());
    }

    /**
     * @param AppliedChangesLogModel[] $logs
     */
    private function show($logs) {

        $table = new Table('', $this->console);
        /** @var AppliedChangesLogModel $appliedLog */
        foreach ($logs as $appliedLog) {
            if ($appliedLog->isFailed()) {
                $table->addColorRow(array(
                    $appliedLog->getDate()->format('d.m.Y H:i:s'),
                    $appliedLog->getName(),
                    $appliedLog->getHash(),
                    'Error: ' . $appliedLog->getErrorMessage()
                ), Console::OUTPUT_ERROR);
            } elseif($appliedLog->isSkipped()) {
                $table->addColorRow(array(
                    $appliedLog->getDate()->format('d.m.Y H:i:s'),
                    $appliedLog->getName(),
                    $appliedLog->getHash(),
                    'skipped'
                ), Console::OUTPUT_PROGRESS);
            } else {
                $table->addColorRow(array(
                    $appliedLog->getDate()->format('d.m.Y H:i:s'),
                    $appliedLog->getName(),
                    $appliedLog->getHash(),
                    $this->console->formatTime($appliedLog->getTime())
                ), Console::OUTPUT_SUCCESS);
            }
        }
        $this->console->printLine($table);
    }

}
