<?php

namespace WS\ReduceMigrations;

use WS\ReduceMigrations\Console\RuntimeFixCounter;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\SetupLogModel;
use WS\ReduceMigrations\Exceptions\MultipleEqualHashException;
use WS\ReduceMigrations\Exceptions\NothingToApplyException;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class MigrationRollback {
    private $runtimeFixCounter;
    private $scenarioDir;
    /**
     * @var MessageOutputInterface
     */
    private $output;

    public function __construct($scenarioDir, MessageOutputInterface $output) {
        $this->scenarioDir = $scenarioDir;
        $this->output = $output;
    }

    /**
     * @param $count
     * @param \Closure|bool $callback
     *
     * @throws NothingToApplyException
     */
    public function rollbackLastFewMigrations($count, $callback = false) {
        $logs = AppliedChangesLogModel::findLastFewMigrations($count);

        if (empty($logs)) {
            throw new NothingToApplyException(sprintf('Nothing to rollback'));
        }

        $setupLogIds = array();
        /** @var AppliedChangesLogModel $log */
        foreach ($logs as $log) {
            $setupLogIds[] = $log->getSetupLogId();
        }
        $setupLogIds = array_unique($setupLogIds);

        $this->rollbackByLogs($logs, $callback);

        foreach ($setupLogIds as $setupLogId) {
            if (!AppliedChangesLogModel::hasMigrationsWithLog($setupLogId)) {
                SetupLogModel::deleteById($setupLogId);
            }
        }

    }

    /**
     * @param $toHash
     * @param \Closure|bool $callback
     *
     * @throws NothingToApplyException
     */
    public function rollbackToHash($toHash, $callback = false) {
        $logs = AppliedChangesLogModel::findToHash($toHash);
        if (empty($logs)) {
            throw new NothingToApplyException(sprintf('Nothing to rollback'));
        }

        $setupLogIds = array();
        /** @var AppliedChangesLogModel $log */
        foreach ($logs as $log) {
            $setupLogIds[] = $log->getSetupLogId();
        }
        $setupLogIds = array_unique($setupLogIds);

        $this->rollbackByLogs($logs, $callback);

        foreach ($setupLogIds as $setupLogId) {
            if (!AppliedChangesLogModel::hasMigrationsWithLog($setupLogId)) {
                SetupLogModel::deleteById($setupLogId);
            }
        }
    }

    /**
     * @param $callback
     *
     * @throws \WS\ReduceMigrations\Exceptions\NothingToApplyException
     */
    public function rollbackLastBatch($callback = false) {
        $logs = AppliedChangesLogModel::findLastBatch();
        if (empty($logs)) {
            throw new NothingToApplyException(sprintf('Nothing to rollback'));
        }
        $this->rollbackByLogs($logs, $callback);
        Module::getInstance()->getLastSetupLog()->delete();
    }

    /**
     * @param $migrationHash
     * @param \Closure|bool $callback
     *
     * @throws MultipleEqualHashException
     * @throws NothingToApplyException
     */
    public function rollbackByHash($migrationHash, $callback = false) {
        $logs = AppliedChangesLogModel::findByHash($migrationHash);
        if (count($logs) > 1) {
            throw new MultipleEqualHashException(sprintf('Found %s migrations with hash `%s`', count($logs), $migrationHash));
        }
        if (empty($logs)) {
            throw new NothingToApplyException(sprintf('Not found migration with hash `%s`', $migrationHash));
        }

        $setupLogIds = array();
        /** @var AppliedChangesLogModel $log */
        foreach ($logs as $log) {
            $setupLogIds[] = $log->getSetupLogId();
        }
        $setupLogIds = array_unique($setupLogIds);

        $this->rollbackByLogs($logs, $callback);

        foreach ($setupLogIds as $setupLogId) {
            if (!AppliedChangesLogModel::hasMigrationsWithLog($setupLogId)) {
                SetupLogModel::deleteById($setupLogId);
            }
        }
    }

    /**
     * @param AppliedChangesLogModel[] $list
     * @param callable|bool $callback
     *
     * @return null
     */
    private function rollbackByLogs($list, $callback = false) {
        $this->runtimeFixCounter = new RuntimeFixCounter();
        $this->runtimeFixCounter->setFixNamesByLogs($list);
        is_callable($callback) && $callback($this->runtimeFixCounter->migrationCount, 'setCount');
        $this->runtimeFixCounter->activeFixName = '';
        $this->runtimeFixCounter->fixNumber = 0;
        $this->runtimeFixCounter->time = microtime(true);

        foreach ($list as $log) {
            $log->delete();
            if ($log->isFailed()) {
                continue;
            }
            if ($log->isSkipped()) {
                continue;
            }
            $time = microtime(true);
            $callbackData = array(
                'name' => $log->getName(),
            );
            is_callable($callback) && $callback($callbackData, 'start');
            $error = '';
            try {
                $class = $log->getMigrationClassName();
                if (!class_exists($class)) {
                    require $this->scenarioDir . DIRECTORY_SEPARATOR . $class . '.php';
                }
                if (!is_subclass_of($class, '\WS\ReduceMigrations\Scenario\ScriptScenario')) {
                    continue;
                }
                $data = $log->getUpdateData();
                /** @var ScriptScenario $object */
                $object = new $class($data, $this->output);
                $object->rollback();

            } catch (\Exception $e) {
                $error = "Exception:" . $e->getMessage();
            }
            $callbackData = array(
                'time' => microtime(true) - $time,
                'log' => $log,
                'error' => $error,
            );
            is_callable($callback) && $callback($callbackData, 'end');
        }
    }

}