<?php

namespace WS\ReduceMigrations;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use ReflectionClass;
use WS\ReduceMigrations\Collection\MigrationCollection;
use WS\ReduceMigrations\Entities\SetupLogModel;
use WS\ReduceMigrations\Exceptions\MultipleEqualHashException;
use WS\ReduceMigrations\Exceptions\NothingToApplyException;

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

    private $applier;

    private $rollback;

    private $scenariosMessageOutput;

    private function __construct() {
        $langPath = Path::normalize(__DIR__) . '/../lang';

        $this->localizePath = $langPath . '/' . LANGUAGE_ID;

        if (!file_exists($this->localizePath)) {
            $this->localizePath = $langPath . '/' . self::FALLBACK_LOCALE;
        }

        $this->scenariosMessageOutput = new DumbMessageOutput();
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
    private function getScenariosDir() {
        return Application::getDocumentRoot() . static::getOptions()->catalogPath;
    }

    /**
     * Return list of not applied migration classes
     *
     * @return MigrationCollection
     */
    public function getNotAppliedScenarios() {
        $applier = $this->getApplier();
        return $applier->getMigrationList();
    }

    /**
     * @param bool $skipOptional
     * @param bool|callable $callback
     *
     * @return int
     */
    public function applyMigrations($skipOptional, $callback = false) {
        $applier = $this->getApplier();
        $applier->skipOptional($skipOptional);
        return $applier->applyMigrations($callback);
    }

    /**
     * @param $migrationHash
     * @param \Closure|bool $callback
     *
     * @throws MultipleEqualHashException
     * @throws NothingToApplyException
     */
    public function applyMigrationByHash($migrationHash, $callback = false) {
        $applier = $this->getApplier();
        $applier->skipOptional(false);
        $applier->applyMigrationByHash($migrationHash, $callback);
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
     * @param $name
     * @param $priority
     * @param $time
     *
     * @return string
     */
    public function createScenario($name, $priority, $time) {
        $templateContent = file_get_contents( $this->getModuleDir() . '/data/scenarioTemplate.tpl');
        $arReplace = array(
            '#class_name#' => $className = 'ws_m_' . time(). '_' . \CUtil::translit($name, LANGUAGE_ID),
            '#name#' => addslashes($name),
            '#priority#' => $this->getPriorityConstant($priority),
            '#time#' => (int)$time,
            '#hash#' => sha1($className),
        );
        $classContent = str_replace(array_keys($arReplace), array_values($arReplace), $templateContent);
        $fileName = $className . '.php';

        return $this->putScriptClass($fileName, $classContent);
    }

    /**
     * @param $priority
     *
     * @return string
     * @throws \Exception
     */
    private function getPriorityConstant($priority) {
        $obj = new ReflectionClass(\WS\ReduceMigrations\Scenario\ScriptScenario::className());

        $priorityList = array();
        foreach ($obj->getConstants() as $cName => $cValue) {
            if (strpos($cName, 'PRIORITY') === 0) {
                $priorityList[$cName] = $cValue;
            }
        }

        $constantName = array_search($priority, $priorityList, true);

        if (!$constantName) {
            throw new \Exception('Priority not found');
        }
        return 'self::' . $constantName;
    }

    /**
     * @param $fileName
     * @param $content
     *
     * @return string
     * @throws \Exception
     */
    private function putScriptClass($fileName, $content) {
        $file = new File($this->getScenariosDir() . DIRECTORY_SEPARATOR . $fileName);
        $success = $file->putContents($content);
        if (!$success) {
            throw new \Exception("Couldn't save file");
        }

        return $file->getPath();
    }

    /**
     * @return string - module root directory
     */
    public function getModuleDir() {
        return Path::getDirectory(Path::normalize(__DIR__));
    }

    /**
     * @param $count
     * @param \Closure|bool $callback
     *
     * @throws NothingToApplyException
     */
    public function rollbackLastFewMigrations($count, $callback = false) {
        $rollback = $this->getRollBack();
        $rollback->rollbackLastFewMigrations($count, $callback);
    }

    /**
     * @param $toHash
     * @param \Closure|bool $callback
     *
     * @throws NothingToApplyException
     */
    public function rollbackToHash($toHash, $callback = false) {
        $rollback = $this->getRollBack();
        $rollback->rollbackToHash($toHash, $callback);
    }

    /**
     * @param $callback
     *
     */
    public function rollbackLastBatch($callback = false) {
        $rollback = $this->getRollBack();
        $rollback->rollbackLastBatch($callback);
    }

    /**
     * @param $migrationHash
     * @param \Closure|bool $callback
     *
     * @throws MultipleEqualHashException
     * @throws NothingToApplyException
     */
    public function rollbackByHash($migrationHash, $callback = false) {
        $rollback = $this->getRollBack();
        $rollback->rollbackByHash($migrationHash, $callback);
    }

    /**
     * @return MigrationApplier
     */
    private function getApplier() {
        if ($this->applier) {
            return $this->applier;
        }
        $this->applier = new MigrationApplier($this->getScenariosDir(), $this->scenariosMessageOutput);
        return $this->applier;
    }

    /**
     * @return MigrationRollback
     */
    private function getRollBack() {
        if ($this->rollback) {
            return $this->rollback;
        }
        $this->rollback = new MigrationRollback($this->getScenariosDir(), $this->scenariosMessageOutput);
        return $this->rollback;
    }

    /**
     * @param MessageOutputInterface $scenariosMessageOutput
     */
    public function setScenariosMessageOutput(MessageOutputInterface $scenariosMessageOutput) {
        $this->scenariosMessageOutput = $scenariosMessageOutput;
    }
}


function jsonToArray($json) {
    /** @var \CMain $APPLICATION */
    global $APPLICATION;
    $value = json_decode($json, true);
    $value = $APPLICATION->ConvertCharsetArray($value, 'UTF-8', LANG_CHARSET);

    return $value;
}

function arrayToJson($data) {
    /** @var \CMain $APPLICATION */
    global $APPLICATION;
    $data = $APPLICATION->ConvertCharsetArray($data, LANG_CHARSET, 'UTF-8');

    return json_encode($data);
}