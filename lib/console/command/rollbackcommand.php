<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\ConsoleException;
use WS\ReduceMigrations\Console\Pear\ConsoleTable;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Timer;

class RollbackCommand extends BaseCommand {
    const TYPE_HASH = 0;
    const TYPE_COUNT = 1;
    const TYPE_TO_HASH = 2;
    const TYPE_LAST_BATCH = 3;

    private $migrationHash;
    private $count;
    private $toHash;
    private $type;

    const PARAM_COUNT = '--count';
    const PARAM_TO_HASH = '--to-hash';
    /** @var Timer  */
    private $timer;

    public function __construct(Console $console, $params) {
        parent::__construct($console, $params);
        $this->timer = new Timer();
    }

    protected function initParams($params) {
        $this->migrationHash = isset($params[0]) ? $params[0] : null;
        $this->count = isset($params[self::PARAM_COUNT]) ? (int)$params[self::PARAM_COUNT] : null;
        if ($this->count && $this->count < 0) {
            $this->count = 0;
        }
        $this->toHash = isset($params[self::PARAM_TO_HASH]) ? $params[self::PARAM_TO_HASH] : null;
        $this->type = $this->identifyType();
    }

    private function identifyType() {
        if ($this->migrationHash) {
            return self::TYPE_HASH;
        }
        if ($this->count) {
            return self::TYPE_COUNT;
        }
        if ($this->toHash) {
            return self::TYPE_TO_HASH;
        }

        return self::TYPE_LAST_BATCH;
    }

    public function execute($callback = false) {

        try {
            $this->rollback($callback);
        } catch (\Exception $e) {
            throw new ConsoleException($e->getMessage());
        }

        $this->timer->stop();
        $time = $this->console->formatTime($this->timer->getTime());
        $this->console
            ->printLine("Rollback action finished! Time $time", Console::OUTPUT_PROGRESS);
    }

    private function rollback($callback = false) {
        switch ($this->type) {
            case self::TYPE_HASH:
                $this->showBatch(AppliedChangesLogModel::findByHash($this->migrationHash));
                $this->confirm("Rollback migration with hash={$this->migrationHash}.");
                $this->timer->start();
                $this->module->rollbackByHash($this->migrationHash);
                break;
            case self::TYPE_COUNT:
                $this->showBatch(AppliedChangesLogModel::findLastFewMigrations($this->count));
                $this->confirm("Rollback last {$this->count} migrations.");
                $this->timer->start();
                $this->module->rollbackLastFewMigrations($this->count, $callback);
                break;
            case self::TYPE_TO_HASH:
                $this->showBatch(AppliedChangesLogModel::findToHash($this->toHash));
                $this->confirm("Rollback migrations to hash={$this->toHash}.");
                $this->timer->start();
                $this->module->rollbackToHash($this->toHash, $callback);
                break;
            case self::TYPE_LAST_BATCH:
                $this->showBatch(AppliedChangesLogModel::findLastBatch());
                $this->confirm('Rollback last batch.');
                $this->timer->start();
                $this->module->rollbackLastBatch($callback);
                break;
        }

    }

    private function confirm($message) {
        $this->console
            ->printLine($message . ' Are you sure? (yes|no):');

        $answer = $this->console
            ->readLine();

        if ($answer !== self::CONFIRM_WORD) {
            throw new ConsoleException('Operation cancelled');
        }

        $this->console
            ->printLine('Rollback action started...', Console::OUTPUT_PROGRESS);
    }

    /**
     * @param AppliedChangesLogModel[] $logs
     */
    private function showBatch($logs) {
        if (empty($logs)) {
            return;
        }
        $table = new ConsoleTable();
        $table->setCharset(LANG_CHARSET);

        $table->setHeaders(array(
            'Date', 'Name', 'Hash', 'Status'
        ));
        $table->setCellsLength(array(
            19, 80, 10, 10
        ));
        foreach ($logs as $log) {
            $status = 'successful';
            if ($log->isSkipped()) {
                $status = 'skipped';
            } elseif ($log->isFailed()) {
                $status = 'failed';
            }
            $table->addRow(array(
                $log->getDate()->format('d.m.Y H:i:s'), $log->getName(), $log->getHash(), $status
            ));
        }
        $table->addRow(array(
            '-------------------', '---------------------', '----------', '----------'
        ));
        $table->addRow(array(
            '', 'Total: '.count($logs)
        ));
        $this->console
            ->printLine('Migrations for rollback:')
            ->printLine($table->getTable());
    }

}
