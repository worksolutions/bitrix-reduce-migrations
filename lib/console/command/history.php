<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;
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
        /** @var AppliedChangesLogModel $appliedLog */
        foreach ($logs as $appliedLog) {
            $message = sprintf('%s "%s" `%s` %s min',
                $appliedLog->getDate()->format('d.m.Y H:i:s'),
                $appliedLog->getName(), $appliedLog->getHash(),
                $appliedLog->getTime()
            );
            $type = Console::OUTPUT_SUCCESS;
            if ($appliedLog->isFailed()) {
                $message = sprintf('%s "%s" `%s` Error: "%s"',
                    $appliedLog->getDate()->format('d.m.Y H:i:s'),
                    $appliedLog->getName(),
                    $appliedLog->getHash(),
                    $appliedLog->getErrorMessage()
                );
                $type = Console::OUTPUT_ERROR;
            } elseif($appliedLog->isSkipped()) {
                $message = sprintf('%s "%s" `%s` - skipped',
                    $appliedLog->getDate()->format('d.m.Y H:i:s'),
                    $appliedLog->getName(), $appliedLog->getHash()
                );
                $type = false;
            }
            $this->console->printLine($message, $type);
        }
    }

}
