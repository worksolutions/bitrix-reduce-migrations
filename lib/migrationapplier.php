<?php

namespace WS\ReduceMigrations;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use WS\ReduceMigrations\Collection\MigrationCollection;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\AppliedChangesLogTable;
use WS\ReduceMigrations\Entities\SetupLogModel;
use WS\ReduceMigrations\Exceptions\MultipleEqualHashException;
use WS\ReduceMigrations\Exceptions\NothingToApplyException;
use WS\ReduceMigrations\Scenario\Exceptions\ApplyScenarioException;
use WS\ReduceMigrations\Scenario\Exceptions\SkipScenarioException;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class MigrationApplier {

    private $notAppliedScenarios;
    private $skipOptional = false;
    private $setupLog;
    protected $migrationFileAllowedExt = array('php');
    /**
     * @var MessageOutputInterface
     */
    private $output;

    public function __construct($scenarioDir, MessageOutputInterface $output) {
        $this->scenarioDir = $scenarioDir;
        $userId = Module::getInstance()->getCurrentUser()->GetID();
        $this->userId = $userId ? : 0;
        $this->output = $output;
    }

    /**
     * @return SetupLogModel
     */
    private function useSetupLog() {
        if (!$this->setupLog) {
            $setupLog = new SetupLogModel();
            $setupLog->userId = $this->userId;
            $setupLog->save();
            $this->setupLog = $setupLog;
        }

        return $this->setupLog;
    }

    /**
     * @param $value
     */
    public function skipOptional($value) {
        $this->skipOptional = $value;
    }

    /**
     * @return MigrationCollection
     */
    public function getMigrationList() {
        if ($this->notAppliedScenarios) {
            return $this->notAppliedScenarios;
        }
        /** @var File[] $files */
        $files = $this->getNotAppliedFiles();
        $this->notAppliedScenarios = new MigrationCollection($files);
        return $this->notAppliedScenarios;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\IO\FileNotFoundException
     */
    private function getNotAppliedFiles() {
        $usesGroups = $this->getAppliedMigrations();

        $dir = new Directory($this->scenarioDir);

        if (!$dir->isExists()) {
            return array();
        }

        $files = array();
        foreach ($dir->getChildren() as $file) {
            $name = $file->getName();
            if ($file->isDirectory()) {
                continue;
            }
            if (in_array($name, $usesGroups)) {
                continue;
            }
            if(!$this->isCorrectMigrationFile($name)) {
                continue;
            }
            $files[$file->getName()] = $file;
        }
        ksort($files);

        return $files;
    }

    /**
     * @return array
     */
    private function getAppliedMigrations() {
        $result = AppliedChangesLogTable::getList(array(
            'select' => array('GROUP_LABEL'),
            'group' => array('GROUP_LABEL'),
        ));

        $usesGroups = array_map(function ($row) {
            return $row['GROUP_LABEL'];
        }, $result->fetchAll());

        return $usesGroups;
    }

    /**
     * @param bool|callable $callback
     *
     * @return int
     */
    public function applyMigrations($callback = false) {
        $classes = $this->getMigrationList()->toArray();
        if (!$classes) {
            return 0;
        }
        $count = 0;
        is_callable($callback) && $callback(count($classes), 'setCount');
        /** @var ScriptScenario $class */
        foreach ($classes as $class) {
            $count++;
            $this->applyScenario($class, $callback);
        }

        return $count;
    }

    /**
     * @param $migrationHash
     * @param \Closure|bool $callback
     *
     * @throws MultipleEqualHashException
     * @throws NothingToApplyException
     */
    public function applyMigrationByHash($migrationHash, $callback = false) {
        $list = $this->getMigrationList()->findByHash($migrationHash);
        if (count($list) > 1) {
            throw new MultipleEqualHashException(sprintf('Found %s migrations with hash `%s`', count($list), $migrationHash));
        }
        if (empty($list)) {
            throw new NothingToApplyException(sprintf('Not found migration with hash `%s`', $migrationHash));
        }
        is_callable($callback) && $callback(count($list), 'setCount');
        $this->applyScenario($list[0], $callback);
    }

    /**
     * @param string $fileName
     * @return bool
     */
    protected function isCorrectMigrationFile($fileName) {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        if($ext) {
            return in_array($ext, $this->migrationFileAllowedExt);
        }

        return false;
    }

    /**
     * @param ScriptScenario $class
     * @param $callback
     */
    private function applyScenario($class, $callback) {
        $setupLog = $this->useSetupLog();
        $time = microtime(true);

        $data = array(
            'name' => $class::name(),
        );
        is_callable($callback) && $callback($data, 'start');
        /** @var ScriptScenario $object */
        $applyFixLog = AppliedChangesLogModel::createByParams($setupLog, $class);
        $object = new $class(array(), $this->output);
        try {
            $this->commitScenario($object);
            $applyFixLog->setUpdateData($object->getData());
            $applyFixLog->markAsSuccessful();
            $applyFixLog->setTime(microtime(true) - $time);
        } catch (SkipScenarioException $e) {
            $applyFixLog->markSkipped();
        } catch (ApplyScenarioException $e) {
            $applyFixLog->markAsFailed();
            $error = $e->getMessage();
            $applyFixLog->setErrorMessage($error);
        }
        $applyFixLog->save();

        $data = array(
            'time' => microtime(true) - $time,
            'log' => $applyFixLog,
            'error' => $error ?: '',
        );
        is_callable($callback) && $callback($data, 'end');
    }

    /**
     * @param ScriptScenario $scenario
     *
     * @throws ApplyScenarioException
     * @throws SkipScenarioException
     */
    private function commitScenario($scenario) {
        if ($this->skipOptional && $scenario->isOptional()) {
            throw new SkipScenarioException();
        }
        try {
            $scenario->commit();
        } catch (\Exception $e) {
            throw new ApplyScenarioException($e->getMessage());
        }
    }

}