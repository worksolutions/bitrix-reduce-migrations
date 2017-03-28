<?php

namespace WS\ReduceMigrations;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use WS\ReduceMigrations\Collection\MigrationCollection;
use WS\ReduceMigrations\Console\RuntimeFixCounter;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\AppliedChangesLogTable;
use WS\ReduceMigrations\Entities\SetupLogModel;
use WS\ReduceMigrations\Exceptions\MultipleEqualHashException;
use WS\ReduceMigrations\Exceptions\NothingToApplyException;
use WS\ReduceMigrations\Scenario\Exceptions\ApplyScenarioException;
use WS\ReduceMigrations\Scenario\Exceptions\SkipScenarioException;
use WS\ReduceMigrations\Scenario\ScriptScenario;

/**
 * Class Module
 *
 * @package WS\ReduceMigrations
 */
class Module {

    const FALLBACK_LOCALE = 'en';

    private $localizePath = null;
    private $localizations = array();

    private static $name = 'ws.reducemigrations';

    /**
     * Versions options Cache
     *
     * @var array
     */
    private $_versions;

    /**
     * @var SetupLogModel
     */
    private $_setupLog;

    /**
     * @var PlatformVersion
     */
    private $version;

    /**
     * @var RuntimeFixCounter
     */
    private $runtimeFixCounter;
    /** @var  MigrationCollection */
    private $notAppliedScenarios;

    private function __construct() {
        $this->localizePath = __DIR__ . '/../lang/' . LANGUAGE_ID;

        if (!file_exists($this->localizePath)) {
            $this->localizePath = __DIR__ . '/../lang/' . self::FALLBACK_LOCALE;
        }
    }

    static public function getName($stripDots = false) {
        $res = static::$name;
        if ($stripDots) {
            $res = str_replace('.', '_', $res);
        }

        return $res;
    }

    /**
     * @return ModuleOptions
     */
    static public function getOptions() {
        return ModuleOptions::getInstance();
    }

    /**
     * @return Module
     */
    static public function getInstance() {
        static $self = null;
        if (!$self) {
            $self = new self;
        }

        return $self;
    }

    /**
     * @param $path
     *
     * @throws \Exception
     * @return Localization
     */
    public function getLocalization($path) {
        if (!isset($this->localizations[$path])) {
            $realPath = $this->localizePath . '/' . str_replace('.', '/', $path) . '.php';
            if (!file_exists($realPath)) {
                throw new \Exception('localization by path not found');
            }
            $this->localizations[$path] = new Localization(include $realPath);
        }

        return $this->localizations[$path];
    }

    /**
     * Directory location scenarios
     *
     * @return string
     */
    private function _getScenariosDir() {
        return Application::getDocumentRoot() . $this->getOptions()->catalogPath;
    }

    /**
     * @param $fileName
     * @param $content
     *
     * @return string
     * @throws \Exception
     */
    public function putScriptClass($fileName, $content) {
        $file = new File($this->_getScenariosDir() . DIRECTORY_SEPARATOR . $fileName);
        $success = $file->putContents($content);
        if (!$success) {
            throw new \Exception("Could'nt save file");
        }

        return $file->getPath();
    }

    /**
     * Return list of not applied migration classes
     *
     * @return MigrationCollection
     */
    public function getNotAppliedScenarios() {
        if ($this->notAppliedScenarios) {
            return $this->notAppliedScenarios;
        }
        /** @var File[] $files */
        $files = $this->_getNotAppliedFiles($this->_getScenariosDir());
        $this->notAppliedScenarios = new MigrationCollection($files);
        return $this->notAppliedScenarios;
    }

    /**
     * @return SetupLogModel
     */
    private function _useSetupLog() {
        if (!$this->_setupLog) {
            $setupLog = new SetupLogModel();
            $setupLog->userId = $this->getCurrentUser()->GetID();
            $setupLog->save();
            $this->_setupLog = $setupLog;
        }

        return $this->_setupLog;
    }

    /**
     * @return \CUser
     */
    public function getCurrentUser() {
        global $USER;

        return $USER ?: new \CUser();
    }

    /**
     * @return SetupLogModel
     */
    public function getLastSetupLog() {
        return SetupLogModel::findOne(array(
            'order' => array('date' => 'desc'),
        ));
    }

    /**
     * @param $callback
     *
     * @return null
     */
    public function rollbackLastChanges($callback = false) {
        $setupLog = $this->getLastSetupLog();
        if (!$setupLog) {
            return null;
        }
        $logs = $setupLog->getAppliedLogs() ?: array();
        $this->rollbackByLogs(array_reverse($logs), $callback);
        $setupLog->delete();
    }

    /**
     * @param AppliedChangesLogModel[] $list
     * @param callable|bool $callback
     *
     * @return null
     */
    public function rollbackByLogs($list, $callback = false) {
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
                'name' => $log->description,
            );
            is_callable($callback) && $callback($callbackData, 'start');
            $error = '';
            try {
                $class = $log->subjectName;
                if (!class_exists($class)) {
                    include $this->_getScenariosDir() . DIRECTORY_SEPARATOR . $class . '.php';
                }
                if (!is_subclass_of($class, '\WS\ReduceMigrations\Scenario\ScriptScenario')) {
                    continue;
                }
                $data = $log->updateData;
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

    /**
     * Value db version
     *
     * @return PlatformVersion
     */
    public function getPlatformVersion() {
        if (!$this->version) {
            $this->version = new PlatformVersion($this->getOptions()->getOtherVersions());
        }

        return $this->version;
    }

    /**
     * @param string $owner
     */
    private function _useVersion($owner) {
        if (!$owner) {
            return;
        }
        $this->_versions = $this->_versions ?: $this->getOptions()->getOtherVersions();
        if (array_search($owner, $this->_versions) !== false) {
            $this->_versions[] = $owner;
            $this->getOptions()->otherVersions = $this->_versions;
        }
    }

    /**
     * @param $dir
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\IO\FileNotFoundException
     */
    private function _getNotAppliedFiles($dir) {
        $result = AppliedChangesLogTable::getList(array(
            'select' => array('GROUP_LABEL'),
            'group' => array('GROUP_LABEL'),
        ));

        $usesGroups = array_map(function ($row) {
            return $row['GROUP_LABEL'];
        }, $result->fetchAll());

        $dir = new Directory($dir);

        if (!$dir->isExists()) {
            return array();
        }

        $files = array();
        foreach ($dir->getChildren() as $file) {
            if ($file->isDirectory()) {
                continue;
            }
            if (in_array($file->getName(), $usesGroups)) {
                continue;
            }
            $files[$file->getName()] = $file;
        }
        ksort($files);

        return $files;
    }

    /**
     * @param bool $skipOptional
     * @param bool|callable $callback
     *
     * @return int
     */
    public function applyNewMigrations($skipOptional, $callback = false) {
        $classes = $this->getNotAppliedScenarios();
        if (!$classes) {
            return 0;
        }
        $count = 0;
        /** @var ScriptScenario $class */
        foreach ($classes as $class) {
            $count++;
            $this->applyScenario($class, $skipOptional, $callback);
        }

        return $count;
    }

    private function commitScenario(ScriptScenario $scenario, $skipOptional) {
        if ($skipOptional && $scenario->isOptional()) {
            throw new SkipScenarioException();
        }
        try {
            $scenario->commit();
        } catch (\Exception $e) {
            throw new ApplyScenarioException($e->getMessage());
        }
    }

    /**
     * @param $name
     * @param $priority
     * @param $time
     *
     * @return string
     */
    public function createScrenario($name, $priority, $time) {
        $templateContent = file_get_contents( $this->getModuleDir() . '/data/scenarioTemplate.tpl');

        $arReplace = array(
            '#class_name#' => $className = 'ws_m_' . time(). '_' . \CUtil::translit($name, LANGUAGE_ID),
            '#name#' => addslashes($name),
            '#priority#' => $priority,
            '#time#' => (int)$time,
            '#hash#' => sha1($className),
            '#owner#' => $this->getPlatformVersion()->getOwner(),
        );
        $classContent = str_replace(array_keys($arReplace), array_values($arReplace), $templateContent);
        $fileName = $className . '.php';

        return $this->putScriptClass($fileName, $classContent);
    }

    /**
     * @return string - module root directory
     */
    public function getModuleDir() {
        return Path::getDirectory(__DIR__);
    }

    /**
     * @param $migrationHash
     * @param \Closure|bool $callback
     *
     * @throws MultipleEqualHashException
     * @throws NothingToApplyException
     */
    public function applyMigrationByHash($migrationHash, $callback = false) {
        $list = $this->getNotAppliedScenarios()->findByHash($migrationHash);
        if (count($list) > 1) {
            throw new MultipleEqualHashException(sprintf('Found %s migrations with hash `%s`', count($list), $migrationHash));
        }
        if (empty($list)) {
            throw new NothingToApplyException(sprintf('Not found migration with hash `%s`', $migrationHash));
        }
        $this->applyScenario($list[0], false, $callback);
    }

    /**
     * @param ScriptScenario $class
     * @param $skipOptional
     * @param $callback
     */
    private function applyScenario($class, $skipOptional, $callback) {
        $setupLog = $this->_useSetupLog();
        $time = microtime(true);

        $data = array(
            'name' => $class::name(),
        );
        is_callable($callback) && $callback($data, 'start');
        /** @var ScriptScenario $object */
        $applyFixLog = AppliedChangesLogModel::createByParams($setupLog, $class);
        $this->_useVersion($class::owner());
        $object = new $class(array());
        try {
            $this->commitScenario($object, $skipOptional);
            $applyFixLog->updateData = $object->getData();
            $applyFixLog->markAsSuccessful();
        } catch (SkipScenarioException $e) {
            $applyFixLog->markSkipped();
        } catch (ApplyScenarioException $e) {
            $applyFixLog->markAsFailed();
            $applyFixLog->description .= " Exception:" . $e->getMessage();
            $error = "Exception:" . $e->getMessage();
        }
        $applyFixLog->save();
        $data = array(
            'time' => microtime(true) - $time,
            'log' => $applyFixLog,
            'error' => $error ?: '',
        );
        is_callable($callback) && $callback($data, 'end');
    }

}

function jsonToArray($json) {
    global $APPLICATION;
    $value = json_decode($json, true);
    $value = $APPLICATION->ConvertCharsetArray($value, "UTF-8", LANG_CHARSET);

    return $value;
}

function arrayToJson($data) {
    global $APPLICATION;
    $data = $APPLICATION->ConvertCharsetArray($data, LANG_CHARSET, "UTF-8");

    return json_encode($data);
}
