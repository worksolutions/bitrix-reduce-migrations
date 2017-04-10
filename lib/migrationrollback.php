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

    public function __construct($scenarioDir) {
        $this->scenarioDir = $scenarioDir;
    }

    /**
     * @param $count
     * @param \Closure|bool $callback
     *
     * @throws NothingToApplyException
     */
    public function rollbackLastFewMigrations($count, $callback = false) {
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'limit' => $count,
        ));

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
        $logsByHash = AppliedChangesLogModel::findByHash($toHash);

        if (empty($logsByHash)) {
            throw new NothingToApplyException(sprintf('Nothing to rollback'));
        }
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array('>id' => $logsByHash[0]->getId())
        ));
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
     * @return null
     */
    public function rollbackLastBatch($callback = false) {
        $setupLog = Module::getInstance()->getLastSetupLog();
        if (!$setupLog) {
            return null;
        }
        $logs = $setupLog->getAppliedLogs() ?: array();
        $this->rollbackByLogs(array_reverse($logs), $callback);
        $setupLog->delete();
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
                $object = new $class($data);
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