<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\ConsoleException;
use WS\ReduceMigrations\Console\Pear\ConsoleTable;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;

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

        $table = new ConsoleTable();
        $table->setCharset(LANG_CHARSET);

        $table->setHeaders(array(
            'Date', 'Name', 'Hash', 'Duration'
        ));
        $table->setCellsLength(array(
            19, 80, 10, 10
        ));

        if (!$this->count) {
            $logs = $lastSetupLog->getAppliedLogs();
        } else {
            $logs = AppliedChangesLogModel::find(array(
                'order' => array('id' => 'desc'),
                'limit' => $this->count
            ));
        }

        $count = 0;
        $commonDuration = 0;
        $setupPaddings = $this->getVerticalPaddingsForSetups($logs);
        $currentSetupId = 0;
        /** @var AppliedChangesLogModel $log */
        foreach ($logs as $log) {
            if ($currentSetupId == 0) {
                $currentSetupId = $log->setupLogId;
            }
            if ($currentSetupId != $log->setupLogId) {
                $currentSetupId = $log->setupLogId;
                $table->addRow();
            }
            $date = '';
            if ($setupPaddings[$log->setupLogId] == 0) {
                $date = $log->getDate()->format('d.m.Y H:i:s');
            }
            $setupPaddings[$log->setupLogId]--;

            $duration = $this->console->formatTime($log->getTime());
            $log->isFailed() && $duration = "failed";
            $log->isSkipped() && $duration = "skipped";

            $table->addRow(array(
                $date, $log->getName(), $log->getHash(), $duration
            ));
            if ($log->isFailed()) {
                $table->addRow(array('', 'Error: '.$log->getErrorMessage(), '', ''));
            }
            $count++;
            $commonDuration += $log->getTime();
        }
        $table->addRow(array(
            '-------------------', '----------------------------------------', '----------', '---------'
        ));
        $table->addRow(array(
            '', 'Total: '.$count, '', $this->console->formatTime($commonDuration)
        ));

        $this->console->printLine("{$count} Last applied migrations:");
        $this->console->printLine($table->getTable());
    }


    private function getVerticalPaddingsForSetups($logs) {
        $arTrackSetup = array();
        foreach ($logs as $log) {
            $arTrackSetup[$log->setupLogId]++;
        }
        foreach ($arTrackSetup as & $countRecords) {
            $countRecords = (int) (($countRecords - 1) / 2);
        }
        return $arTrackSetup;
    }
}
