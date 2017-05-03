<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;
use WS\ReduceMigrations\Console\Formatter\Table;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\SetupLogModel;

class History extends BaseCommand {

    private $count;

    const SECONDS_PRECISION = 3;
    const MINUTES_PRECISION = 2;
    const SECONDS_THRESHOLD_VALUE = 100;

    const SECONDS_IN_MINUTE = 60;

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
                    $this->formatTime($appliedLog->getTime())
                ), Console::OUTPUT_SUCCESS);
            }
        }
        $this->console->printLine($table);
    }

    private function formatTime($initialTime) {
        $time = round($initialTime, self::SECONDS_PRECISION);
        if ($time >= self::SECONDS_THRESHOLD_VALUE) {
            $time = round($time / self::SECONDS_IN_MINUTE, self::MINUTES_PRECISION);
            $time = sprintf('%s min', $time);
        } else {
            $time = sprintf('%s sec', $time);
        }
        return $time;
    }

}
