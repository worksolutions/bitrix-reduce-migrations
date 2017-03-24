<?php

namespace WS\ReduceMigrations;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use WS\ReduceMigrations\Console\RuntimeFixCounter;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\AppliedChangesLogTable;
use WS\ReduceMigrations\Entities\SetupLogModel;

/**
 * Class Module
 *
 * @package WS\ReduceMigrations
 */
class Module {

    const SPECIAL_PROCESS_SCENARIO = 'Scenario';

    const FALLBACK_LOCALE = 'en';

    private $localizePath = null;
    private $localizations = array();

    private static $name = 'ws.reducemigrations';

    /**
     * @var bool
     */
    private $_isUsingScenariosUpdate = false;

    private $_usingScenarioName = null;

    private $_usingScenarioCount = 0;

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


    public function isUsingScenariosUpdate() {
        return $this->_isUsingScenariosUpdate;
    }

    /**
     * @return string
     */
    public function createUsingScenarioId() {
        return $this->_usingScenarioName . ($this->_usingScenarioCount++);
    }

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
     * Gets class not applied scenarios
     *
     * @return array
     */
    public function getNotAppliedScenarios() {
        /** @var File[] $files */
        $files = $this->_getNotAppliedFiles($this->_getScenariosDir());

        $res = array();
        foreach ($files as $file) {
            $fileClass = str_replace(".php", "", $file->getName());
            if (!class_exists($fileClass)) {
                include $file->getPath();
            }

            if (!is_subclass_of($fileClass, '\WS\ReduceMigrations\ScriptScenario')) {
                continue;
            }
            if (!$fileClass::isValid()) {
                continue;
            }
            $res[] = $fileClass;
        }

        return $res;
    }

    /**
     * Applies all fixes
     *
     * @param callable|bool $callback
     *
     * @return int
     * @throws \Exception
     */
    public function applyNewFixes($callback = false) {
        if (is_callable($callback)) {
            $callback(count($this->getNotAppliedScenarios()), 'setCount');
        }
        $count = $this->applyNewScenarios($callback) ?: 0;

        return $count;
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
            $processName = $log->processName;
            if ($processName != self::SPECIAL_PROCESS_SCENARIO) {
                continue;
            }
            $log->delete();
            if (!$log->success) {
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
                if (!is_subclass_of($class, '\WS\ReduceMigrations\ScriptScenario')) {
                    continue;
                }
                $data = $log->updateData;
                /** @var ScriptScenario $object */
                $object = new $class($data);

                $this->_usingScenarioCount = 0;
                $this->_isUsingScenariosUpdate = true;
                $this->_usingScenarioName = get_class($object);

                $object->rollback();

                $this->_isUsingScenariosUpdate = false;
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
     * @param bool|callable $callback
     *
     * @return int
     */
    public function applyNewScenarios($callback = false) {
        $classes = $this->getNotAppliedScenarios();
        if (!$classes) {
            return 0;
        }
        $setupLog = $this->_useSetupLog();
        foreach ($classes as $class) {
            $time = microtime(true);
            /** @var ScriptScenario $object */
            $object = new $class(array());
            $data = array(
                'name' => $object->name(),
            );
            is_callable($callback) && $callback($data, 'start');

            $applyFixLog = new AppliedChangesLogModel();
            $applyFixLog->processName = self::SPECIAL_PROCESS_SCENARIO;
            $applyFixLog->subjectName = $class;
            $applyFixLog->setSetupLog($setupLog);
            $applyFixLog->groupLabel = $class . '.php';
            $applyFixLog->description = $object->name();
            list($hash, $owner) = $object->version();
            $applyFixLog->hash = $hash;
            $applyFixLog->owner = $owner;
            $this->_useVersion($owner);
            $error = '';
            try {
                $this->_usingScenarioCount = 0;
                $this->_isUsingScenariosUpdate = true;
                $this->_usingScenarioName = get_class($object);

                $object->commit();
                $applyFixLog->updateData = $object->getData();
                $applyFixLog->success = true;

                $this->_isUsingScenariosUpdate = false;
            } catch (\Exception $e) {
                $this->_isUsingScenariosUpdate = false;
                $applyFixLog->success = false;
                $applyFixLog->description .= " Exception:" . $e->getMessage();
                $error = "Exception:" . $e->getMessage();
            }
            $applyFixLog->save();
            $data = array(
                'time' => microtime(true) - $time,
                'log' => $applyFixLog,
                'error' => $error,
            );
            is_callable($callback) && $callback($data, 'end');
        }

        return count($classes);
    }

    /**
     * @param $name
     * @param $priority
     *
     * @return string
     */
    public function createScrenario($name, $priority) {
        $templateContent = file_get_contents( $this->getModuleDir() . '/data/scenarioTemplate.tpl');

        $arReplace = array(
            '#class_name#' => $className = 'ws_m_' . time(). '_' . \CUtil::translit($name, LANGUAGE_ID),
            '#name#' => addslashes($name),
            '#priority#' => $priority,
            '#hash#' => sha1($className),
            '#owner#' => $this->getPlatformVersion()->getOwner()
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
