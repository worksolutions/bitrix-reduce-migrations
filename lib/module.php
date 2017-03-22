<?php

namespace WS\ReduceMigrations;

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use WS\ReduceMigrations\ChangeDataCollector\Collector;
use WS\ReduceMigrations\ChangeDataCollector\CollectorFix;
use WS\ReduceMigrations\Console\RuntimeFixCounter;
use WS\ReduceMigrations\Diagnostic\DiagnosticTester;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Entities\AppliedChangesLogTable;
use WS\ReduceMigrations\Entities\SetupLogModel;
use WS\ReduceMigrations\Processes\AddProcess;
use WS\ReduceMigrations\Processes\BaseProcess;
use WS\ReduceMigrations\Processes\DeleteProcess;
use WS\ReduceMigrations\Processes\UpdateProcess;
use WS\ReduceMigrations\Reference\OwnReferenceCleaner;
use WS\ReduceMigrations\Reference\ReferenceController;
use WS\ReduceMigrations\Reference\ReferenceItem;
use WS\ReduceMigrations\SubjectHandlers\BaseSubjectHandler;
use WS\ReduceMigrations\SubjectHandlers\IblockHandler;
use WS\ReduceMigrations\SubjectHandlers\IblockPropertyHandler;
use WS\ReduceMigrations\SubjectHandlers\IblockSectionHandler;
use WS\ReduceMigrations\Tests\Starter;

/**
 * Class Module
 *
 * @package WS\ReduceMigrations
 */
class Module {

    const FIX_CHANGES_BEFORE_ADD_KEY    = 'beforeAdd';
    const FIX_CHANGES_AFTER_ADD_KEY     = 'afterAdd';
    const FIX_CHANGES_BEFORE_CHANGE_KEY = 'beforeChange';
    const FIX_CHANGES_AFTER_CHANGE_KEY  = 'afterChange';
    const FIX_CHANGES_BEFORE_DELETE_KEY = 'beforeDelete';
    const FIX_CHANGES_AFTER_DELETE_KEY  = 'afterDelete';

    const SPECIAL_PROCESS_FIX_REFERENCE = 'reference';
    const SPECIAL_PROCESS_FULL_MIGRATE  = 'fullMigrate';
    const SPECIAL_PROCESS_SCENARIO = 'Scenario';

    const REFERENCE_SUBJECT_ADD    = 'add';
    const REFERENCE_SUBJECT_REMOVE = 'remove';

    const FALLBACK_LOCALE = 'en';

    private $localizePath = null,
        $localizations = array();

    private static $name = 'ws.migrations';

    private $_handlers = array();

    /**
     * @var Collector
     */
    private $_dutyCollector = null;

    /**
     * @var ReferenceController
     */
    private $_referenceController = null;

    private $_processAdd;
    private $_processUpdate;
    private $_processDelete;

    private $_listenMode = true;

    /**
     * @var bool
     */
    private $_isUsingScenariosUpdate = false;

    private $_usingScenarioName = null;

    private $_usingScenarioCount = 0;

    /**
     * Versions options Cache
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
     * @var DiagnosticTester
     */
    private $diagnostic;
    /**
     * @var RuntimeFixCounter
     */
    private $runtimeFixCounter;

    /**
     * @return $this
     */
    private function _enableListen() {
        $this->_listenMode = true;
        return $this;
    }

    /**
     * @return $this
     */
    private function _disableListen() {
        $this->_listenMode = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasListen() {
        return (bool)$this->_listenMode;
    }

    public function isUsingScenariosUpdate() {
        return $this->_isUsingScenariosUpdate;
    }

    /**
     * @return string
     */
    public function createUsingScenarioId() {
        return $this->_usingScenarioName.($this->_usingScenarioCount++);
    }

    private function __construct() {
        $this->localizePath = __DIR__.'/../lang/'.LANGUAGE_ID;

        if (!file_exists($this->localizePath)) {
            $this->localizePath = __DIR__.'/../lang/'.self::FALLBACK_LOCALE;
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

    static public function listen(){
        EventManager::getInstance()
            ->addEventHandler('main', 'OnBeforeLocalRedirect', array(get_called_class(), 'commitDutyChanges'));
        EventManager::getInstance()
            ->addEventHandler('main', 'OnCheckListGet', array(Starter::className(), 'items'));
        $self = self::getInstance();
        $bxEventManager = EventManager::getInstance();
        foreach ($self->handlers() as $class => $events) {
            foreach ($events as $eventKey => $eventData) {
                $bxEventManager->addEventHandler($eventData[0], $eventData[1], new Callback(function () {
                    $params = func_get_args();
                    /** @var $module Module */
                    $module = array_shift($params);
                    $handlerClass = array_shift($params);
                    $eventKey = array_shift($params);
                    try {
                        $diagnostic = $module->useDiagnostic();
                        if (!$diagnostic->hasRun()) {
                            $diagnostic->run();
                        }
                        if (!$diagnostic->isSuccessRunResult()) {
                            throw new \Exception("Migrations module: diagnostic not success. Project has problems. More in diagnostic page.");
                        }
                        $module->handle($handlerClass, $eventKey, $params);
                    } catch (\Exception $e) {
                        /** @var \CMain $APPLICATION */
                        global $APPLICATION;
                        $APPLICATION->ThrowException($e->getMessage());
                        return false;
                    }
                    return true;
                }, $self, $class, $eventKey));
            }
        }
        $self->_referenceController = new ReferenceController($self->getPlatformVersion()->getValue());
        $fixRefProcess = self::SPECIAL_PROCESS_FIX_REFERENCE;

        $fSetupReferenceFix = function (ReferenceItem $item, $subject, $priority) use ($self, $fixRefProcess) {
            $collector = $self->getDutyCollector();
            $fix = $collector->createFix();
            $fix
                ->setName('Reference fix')
                ->setProcess($fixRefProcess)
                ->setSubject($subject)
                ->setUpdateData(array(
                    'reference' => $item->reference,
                    'group' => $item->group,
                    'dbVersion' => $item->dbVersion,
                    'id' => $item->id
                ));
            $collector->registerFix($fix, $priority);
        };

        $referenceAddSubject = self::REFERENCE_SUBJECT_ADD;
        $referenceRemoveSubject = self::REFERENCE_SUBJECT_REMOVE;

        $refCollector = $self->getReferenceController();
        $refCollector->onRegister(function (ReferenceItem $item) use ($fSetupReferenceFix, $referenceAddSubject, $self, $refCollector) {
            if ($self->isUsingScenariosUpdate()) {
                $item->reference = $refCollector->createReferenceStringValue($self->createUsingScenarioId());
            }
            $fSetupReferenceFix($item, $referenceAddSubject, Collector::ORDER_PRIORITY_MIDDLE);
        });

        $self->getReferenceController()->onRemove(function (ReferenceItem $item) use ($fSetupReferenceFix, $referenceRemoveSubject) {
            $fSetupReferenceFix($item, $referenceRemoveSubject, Collector::ORDER_PRIORITY_LOW);
        });
    }

    /**
     * @param CollectorFix[] $fixes
     * @throws \Exception
     */
    private function _logOwnChanges($fixes) {
        $hasRealChanges = (bool) array_filter($fixes, function ($item) {
            if (!$item instanceof CollectorFix) {
                return false;
            }
            return $item->getProcess() != Module::SPECIAL_PROCESS_FIX_REFERENCE;
        });
        $setupLog = null;
        if ($hasRealChanges) {
            $setupLog = $this->_useSetupLog();
        }
        /** @var CollectorFix $fix */
        foreach ($fixes as $fix) {
            $applyLog = new AppliedChangesLogModel();
            $applyLog->subjectName = $fix->getSubject();
            $applyLog->groupLabel = $fix->getLabel();
            $applyLog->processName = $fix->getProcess();
            $applyLog->description = $fix->getName();
            $applyLog->originalData = $fix->getOriginalData();
            $applyLog->updateData = $fix->getUpdateData();
            $applyLog->source = $this->getPlatformVersion()->getValue();
            $applyLog->success = true;
            $applyLog->setSetupLog($setupLog);

            if ($fix->getProcess() == self::SPECIAL_PROCESS_FIX_REFERENCE) {
                $applyLog->description = 'Insert reference';
                $applyLog->subjectName = 'reference';
            }
            if (!$applyLog->save()) {
                throw new \Exception('Not save current changes in log ' . var_export($applyLog->getErrors()));
            }
        }
    }

    static public function commitDutyChanges() {
        $self = self::getInstance();
        if (!$self->_dutyCollector) {
            return null;
        }
        $fixes = $self->getDutyCollector()->getUsesFixed();
        if (!$fixes) {
            return;
        }
        $self->_logOwnChanges($fixes);
        $platformVersion = $self->getPlatformVersion();
        $self->getDutyCollector()->commit($platformVersion->getValue(), $platformVersion->getOwner());
    }

    /**
     * @param $path
     * @throws \Exception
     * @return Localization
     */
    public function getLocalization($path) {
        if (!isset($this->localizations[$path])) {
            $realPath = $this->localizePath.'/'.str_replace('.', '/',$path).'.php';
            if (!  file_exists($realPath)) {
                throw new \Exception('localization by path not found');
            }
            $this->localizations[$path] = new Localization(include $realPath);
        }
        return $this->localizations[$path];
    }

    /**
     * @return DiagnosticTester
     */
    public function useDiagnostic() {
        if (!$this->diagnostic) {
            $handlers = array();
            foreach ($this->getSubjectHandlers() as $handler) {
                $this->isEnableSubjectHandler(get_class($handler)) && $handlers[] = $handler;
            }
            $this->diagnostic = new DiagnosticTester($handlers, $this);

            $dbVersion = $this->getOptions()->dbPlatformVersion;
            if ($dbVersion && $dbVersion != $this->getPlatformVersion()->getValue()) {
                $this->getReferenceController()->fitVersion($dbVersion);
                $this->getOptions()->dbPlatformVersion = $this->getPlatformVersion()->getValue();
            }
            !$dbVersion && ($this->getOptions()->dbPlatformVersion = $this->getPlatformVersion()->getValue());
        }
        return $this->diagnostic;
    }

    /**
     * Meta description uses handlers, been register
     * @return array
     */
    protected function handlers() {
        return array(
            IblockHandler::className() => array(
                self::FIX_CHANGES_BEFORE_ADD_KEY => array('iblock', 'OnBeforeIBlockAdd'),
                self::FIX_CHANGES_AFTER_ADD_KEY => array('iblock', 'OnAfterIBlockAdd'),
                self::FIX_CHANGES_BEFORE_CHANGE_KEY => array('iblock', 'OnBeforeIBlockUpdate'),
                self::FIX_CHANGES_AFTER_CHANGE_KEY => array('iblock', 'OnAfterIBlockUpdate'),
                self::FIX_CHANGES_BEFORE_DELETE_KEY => array('iblock', 'OnBeforeIBlockDelete'),
                self::FIX_CHANGES_AFTER_DELETE_KEY => array('iblock', 'OnIBlockDelete'),
            ),
            IblockPropertyHandler::className() => array(
                self::FIX_CHANGES_BEFORE_ADD_KEY => array('iblock', 'OnBeforeIBlockPropertyAdd'),
                self::FIX_CHANGES_AFTER_ADD_KEY => array('iblock', 'OnAfterIBlockPropertyAdd'),
                self::FIX_CHANGES_BEFORE_CHANGE_KEY => array('iblock', 'OnBeforeIBlockPropertyUpdate'),
                self::FIX_CHANGES_AFTER_CHANGE_KEY => array('iblock', 'OnAfterIBlockPropertyUpdate'),
                self::FIX_CHANGES_BEFORE_DELETE_KEY => array('iblock', 'OnBeforeIBlockPropertyDelete'),
                self::FIX_CHANGES_AFTER_DELETE_KEY => array('iblock', 'OnIBlockPropertyDelete')
            ),
            IblockSectionHandler::className() => array(
                self::FIX_CHANGES_BEFORE_ADD_KEY => array('iblock', 'OnBeforeIBlockSectionAdd'),
                self::FIX_CHANGES_AFTER_ADD_KEY => array('iblock', 'OnAfterIBlockSectionAdd'),
                self::FIX_CHANGES_BEFORE_CHANGE_KEY => array('iblock', 'OnBeforeIBlockSectionUpdate'),
                self::FIX_CHANGES_AFTER_CHANGE_KEY => array('iblock', 'OnAfterIBlockSectionUpdate'),
                self::FIX_CHANGES_BEFORE_DELETE_KEY => array('iblock', 'OnBeforeIBlockSectionDelete'),
                self::FIX_CHANGES_AFTER_DELETE_KEY => array('iblock', 'OnAfterIBlockSectionDelete')
            )
        );
    }

    /**
     * @return BaseSubjectHandler[]
     */
    public function getSubjectHandlers() {
        $classes = array_keys($this->handlers());
        $res = array();
        foreach ($classes as $class) {
            $res[] = $this->getSubjectHandler($class);
        }
        return $res;
    }

    /**
     * Returns sign of handlers enable
     * @param string $class Handlers class name instance of \WS\Migrations\SubjectHandlers\BaseSubjectHandler
     * @return bool
     */
    public function isEnableSubjectHandler($class) {
        if (!is_subclass_of($class, BaseSubjectHandler::className())) {
            return false;
        }
        $oneselfEnable = $this->getOptions()->isEnableSubjectHandler($class);
        if (!$oneselfEnable) {
            return false;
        }
        foreach ($class::depends() as $dependClass) {
            if (!$this->isEnableSubjectHandler($dependClass)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Enable subject handler
     * @throws \Exception
     * @param $class
     */
    public function enableSubjectHandler($class) {
        if (!is_subclass_of($class, BaseSubjectHandler::className())) {
            throw new \Exception("class not instance of BaseSubjectHandler");
        }
        $this->getOptions()->enableSubjectHandler($class);
        foreach ($class::depends() as $dependFromClass) {
            $this->enableSubjectHandler($dependFromClass);
        }
    }

    /**
     * Disable subject handler
     * @throws \Exception
     * @param $class
     */
    public function disableSubjectHandler($class) {
        if (!is_subclass_of($class, BaseSubjectHandler::className())) {
            throw new \Exception("class not instance of BaseSubjectHandler");
        }
        $this->getOptions()->disableSubjectHandler($class);
        foreach (array_keys($this->handlers()) as $handlerClass) {
            if (in_array($class, $handlerClass::depends())) {
                $this->disableSubjectHandler($handlerClass);
            }
        }
    }

    /**
     * @param $class
     * @throws \Exception
     * @return BaseSubjectHandler
     */
    public function getSubjectHandler($class) {
        if (! class_exists($class)) {
            foreach (array_keys($this->handlers()) as $handlerClass) {
                $arClassName = explode('\\', $handlerClass);
                if ($class == array_pop($arClassName)) {
                    $class = $handlerClass;
                    break;
                }
            }
        }
        if (!class_exists($class)) {
            throw new \Exception('Class not exists');
        }
        if (!$this->_handlers[$class]) {

            $this->_handlers[$class] = new $class($this->getReferenceController());
        }
        return $this->_handlers[$class];
    }

    /**
     * @return AddProcess
     */
    private function _getProcessAdd() {
        if (!$this->_processAdd) {
            $this->_processAdd = new AddProcess();
        }
        return $this->_processAdd;
    }

    /**
     * @return UpdateProcess
     */
    private function _getProcessUpdate() {
        if (!$this->_processUpdate) {
            $this->_processUpdate = new UpdateProcess();
        }
        return $this->_processUpdate;
    }

    /**
     * @return DeleteProcess
     */
    private function _getProcessDelete() {
        if (!$this->_processDelete) {
            $this->_processDelete = new DeleteProcess();
        }
        return $this->_processDelete;
    }

    /**
     * @param $class
     * @return BaseProcess
     * @throws \Exception
     */
    public function getProcess($class) {
        switch ($class) {
            case AddProcess::className():
                return $this->_getProcessAdd();
                break;
            case UpdateProcess::className():
                return $this->_getProcessUpdate();
                break;
            case DeleteProcess::className():
                return $this->_getProcessDelete();
                break;
        }
        throw new \Exception("Process class $class not recognized");
    }

    /**
     * @param $handlerClass
     * @param $eventKey
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function handle($handlerClass, $eventKey, $params) {

        $isValid = $this->getPlatformVersion()->isValid();
        if ($isValid) {
            $isValid = $this->useDiagnostic()
                ->getLastResult()
                ->isSuccess();
        }
        if (!$this->hasListen()) {
            return false;
        }
        $handlers = $this->handlers();
        if ( !$handlers[$handlerClass][$eventKey]) {
            return false;
        }
        if (!$this->isEnableSubjectHandler($handlerClass)) {
            return false;
        }
        if (!$isValid) {
            throw new \Exception('Module platform version is not valid');
        }
        $collector = $this->getDutyCollector();
        $handler = $this->getSubjectHandler($handlerClass);
        $fix  = $collector->createFix();
        $fix->setSubject(get_class($handler));

        $result = false;
        switch ($eventKey) {
            case self::FIX_CHANGES_BEFORE_ADD_KEY:
                break;
            case self::FIX_CHANGES_AFTER_ADD_KEY:
                $process = $this->_getProcessAdd();
                $fix
                    ->setProcess(get_class($process))
                    ->setName($handler->getName().'. '.$process->getName());
                $result = $process->change($handler, $fix, $params);
                break;
            case self::FIX_CHANGES_BEFORE_CHANGE_KEY:
                $process = $this->_getProcessUpdate();
                $process->beforeChange($handler, $params);
                break;
            case self::FIX_CHANGES_AFTER_CHANGE_KEY:
                $process = $this->_getProcessUpdate();
                $fix
                    ->setProcess(get_class($process))
                    ->setName($handler->getName().'. '.$process->getName());
                $result = $process->afterChange($handler, $fix, $params);
                break;
            case self::FIX_CHANGES_BEFORE_DELETE_KEY:
                $process = $this->_getProcessDelete();
                $process->beforeChange($handler, $params);
                break;
            case self::FIX_CHANGES_AFTER_DELETE_KEY:
                $process = $this->_getProcessDelete();
                $fix
                    ->setProcess(get_class($process))
                    ->setName($handler->getName().'. '.$process->getName());
                $result = $process->afterChange($handler, $fix, $params);
                break;
        }
        if ($result && !$this->isUsingScenariosUpdate()) {
            $collector->registerFix($fix, $this->getFixPriority($process, $handler));
        }
        return true;
    }

    /**
     * @return Collector
     */
    private function _createCollector() {
        return Collector::createInstance($this->_getFixFilesDir());
    }

    /**
     * @return Collector
     */
    public function getDutyCollector() {
        if (!$this->_dutyCollector) {
            $this->_dutyCollector = $this->_createCollector();
        }
        return $this->_dutyCollector;
    }

    private function getReferenceController() {
        if (!$this->_referenceController) {
            $this->_referenceController = new ReferenceController($this->getPlatformVersion()->getValue());
        }
        return $this->_referenceController;
    }

    /**
     * @param Collector $collector
     * @return $this
     */
    public function injectDutyCollector(Collector $collector) {
        $this->_dutyCollector = $collector;
        return $this;
    }

    /**
     * Directory location fixed files
     * @return string
     */
    private function _getFixFilesDir() {
        return Application::getDocumentRoot().DIRECTORY_SEPARATOR.$this->getOptions()->catalogPath;
    }

    /**
     * Directory location scenarios
     * @return string
     */
    private function _getScenariosDir() {
        return $this->_getFixFilesDir().DIRECTORY_SEPARATOR.'scenarios';

    }

    /**
     * @param $fileName
     * @param $content
     * @return string
     * @throws \Exception
     */
    public function putScriptClass($fileName, $content) {
        $file = new File($this->_getScenariosDir().DIRECTORY_SEPARATOR.$fileName);
        $success = $file->putContents($content);
        if (!$success) {
            throw new \Exception("Could'nt save file");
        }
        return $file->getPath();
    }

    /**
     * @return Collector[]
     */
    private function _getNotAppliedCollectors() {
        $files = $this->_getNotAppliedFiles($this->_getFixFilesDir());
        $collectors = array();
        /** @var File $file */
        foreach ($files as $file) {
            $collectors[] = Collector::createByFile($file->getPath());
        }
        return $collectors;
    }

    /**
     * @return CollectorFix[]
     */
    public function getNotAppliedFixes() {
        $collectors = $this->_getNotAppliedCollectors();
        $result = array();
        foreach ($collectors as $collector) {
            $result = array_merge($result, $collector->getFixes() ?: array());
        }
        return $result;
    }

    /**
     * Gets class not applied scenarios
     * @return array
     */
    public function getNotAppliedScenarios() {
        /** @var File[] $files */
        $files = $this->_getNotAppliedFiles($this->_getFixFilesDir().DIRECTORY_SEPARATOR.'scenarios');
        $res = array();
        foreach ($files as $file) {
            $fileClass = str_replace(".php", "", $file->getName());
            if (!class_exists($fileClass)) {
                include $file->getPath();
            }
            if (!is_subclass_of($fileClass, '\WS\Migrations\ScriptScenario')) {
                continue;
            }
            if (!$fileClass::isValid()) {
                continue;
            }
            $res[] = $fileClass;
        }
        return $res;
    }

    private function _applyFix(CollectorFix $fix, AppliedChangesLogModel $applyFixLog = null) {
        $process = $this->getProcess($fix->getProcess());
        $subjectHandlerClass = $fix->getSubject();
        if (!$this->isEnableSubjectHandler($subjectHandlerClass)) {
            $applyFixLog->success = false;
            $applyFixLog->description = 'Subject handler not active';
            $applyFixLog->save();
            return ;
        }
        $subjectHandler = $this->getSubjectHandler($subjectHandlerClass);
        $result = $process->update($subjectHandler, $fix, $applyFixLog);
        $applyFixLog->success = (bool) $result->isSuccess();
        !$result->isSuccess() && $applyFixLog->description .= '. '.$result->getMessage();
    }

    private function _applyReferenceFix(CollectorFix $fix) {
        $item = new ReferenceItem();
        $data = $fix->getUpdateData();

        $subject = $fix->getSubject() ?: self::REFERENCE_SUBJECT_ADD;

        $item->reference = $data['reference'];
        $item->id = $data['id'];
        $item->group = $data['group'];
        $item->dbVersion = $data['dbVersion'];

        if ($subject == self::REFERENCE_SUBJECT_ADD) {
            try {
                $this->getReferenceController()->getReferenceValue($item->id, $item->group, $item->dbVersion);
                $this->getReferenceController()->getItemCurrentVersionByReference($item->reference);
            } catch (\Exception $e) {
                $this->getReferenceController()->registerItem($item);
            }
        }
        if ($subject == self::REFERENCE_SUBJECT_REMOVE) {
            $this->getReferenceController()
                ->removeItemById($item->id, $item->group, $item->dbVersion);
        }
    }

    /**
     * @param $fixes
     * @param bool|callable|false $callback
     * @return int
     * @throws \Exception
     */
    public function applyFixesList($fixes, $callback = false) {
        if (!$this->getPlatformVersion()->isValid()) {
            throw new \Exception('Module platform version is not valid');
        }
        if (!$fixes) {
            return 0;
        }
        $this->_disableListen();
        $setupLog = $this->_useSetupLog();

        $this->runtimeFixCounter = new RuntimeFixCounter();
        $this->runtimeFixCounter->setFixNameByFixes($fixes);
        $this->runtimeFixCounter->activeFixName = '';
        $this->runtimeFixCounter->fixNumber = 0;
        $this->runtimeFixCounter->time = microtime(true);
        $applyFixLog = false;
        /** @var CollectorFix $fix */
        foreach ($fixes as $fix) {
            $this->runFixCallback($callback, $fix->getName(), $applyFixLog);
            $applyFixLog = new AppliedChangesLogModel();
            try {
                $applyFixLog->processName = $fix->getProcess();
                $applyFixLog->subjectName = $fix->getSubject();
                $applyFixLog->setSetupLog($setupLog);
                $applyFixLog->groupLabel = $fix->getLabel();
                $applyFixLog->description = 'References updates';
                $applyFixLog->source = $fix->getDbVersion();

                $this->_useAnotherVersion($fix);
                if ($this->isReferenceFix($fix)) {
                    $applyFixLog->subjectName = 'references';
                    $applyFixLog->success = true;
                    $this->_applyReferenceFix($fix);
                } else {
                    $this->_applyFix($fix, $applyFixLog);
                }
            } catch (\Exception $e) {
                $applyFixLog->success = false;
                $applyFixLog->description = arrayToJson(array(
                    'message' => $fix->getName().': '.$e->getMessage(),
                    'data' => $fix->getUpdateData(),
                    'trace' => $e->getTrace()
                ));
            }
            $applyFixLog->save();
        }
        if (is_callable($callback) && $applyFixLog) {
            $data = array(
                'time' => microtime(true) - $this->runtimeFixCounter->time,
                'log' => $applyFixLog,
                'error' => ''
            );
            $callback($data, 'end');
        }

        $this->_enableListen();
        return count($fixes);
    }

    public function isReferenceFix(CollectorFix $fix) {
        return $fix->getProcess() == self::SPECIAL_PROCESS_FIX_REFERENCE;
    }

    /**
     * @param BaseProcess $process
     * @param BaseSubjectHandler $handler
     * @return string
     */
    private function getFixPriority(BaseProcess $process, BaseSubjectHandler $handler) {
        if ($process instanceof DeleteProcess && $handler instanceof IblockHandler) {
            return Collector::ORDER_PRIORITY_LOW;
        }
        return Collector::ORDER_PRIORITY_MIDDLE;
    }

    /**
     * Apply references by records another versions
     */
    public function applyAnotherReferences() {
        if (!$this->getPlatformVersion()->isValid()) {
            throw new \Exception('Module platform version is not valid');
        }
        $fixes = $this->getNotAppliedFixes();
        foreach ($fixes as $fix) {
            if (!$this->isReferenceFix($fix)) {
                continue;
            }
            $this->_applyReferenceFix($fix);
        }
    }

    /**
     * Try is getting a snapshot. This version data can be version for added.
     * @param CollectorFix $fix
     * @return array|mixed
     */
    public function getSnapshotDataByFix(CollectorFix $fix) {
        try {
            $process = $this->getProcess($fix->getProcess());
            if ($process instanceof AddProcess) {
                return array();
            }
            $subjectHandler = $this->getSubjectHandler($fix->getSubject());
            $id = null;
            if ($process instanceof UpdateProcess) {
                $id = $subjectHandler->getIdBySnapshot($fix->getUpdateData());
            }
            if ($process instanceof DeleteProcess) {
                $id = $fix->getUpdateData();
            }
            return $id ? $subjectHandler->getSnapshot($id, $fix->getDbVersion()) : array();
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Applies all fixes
     * @param callable|bool $callback
     * @return int
     * @throws \Exception
     */
    public function applyNewFixes($callback = false) {
        if (is_callable($callback)) {
            $countFixes = 0;
            $prevName = '';
            foreach ($this->getNotAppliedFixes() as $fix) {
                if ($prevName == $fix->getName()) {
                    continue;
                }
                $countFixes++;
                $prevName = $fix->getName();
            }
            $callback(count($this->getNotAppliedScenarios()) + $countFixes, 'setCount');
        }
        $count = $this->applyFixesList($this->getNotAppliedFixes(), $callback) ?: 0;
        $count += $this->applyNewScenarios($callback) ?: 0;
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
            'order' => array('date' => 'desc')
        ));
    }

    /**
     * @param $callback
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
     * @return null
     */
    public function rollbackByLogs($list, $callback = false) {
        $this->_disableListen();
        $this->runtimeFixCounter = new RuntimeFixCounter();
        $this->runtimeFixCounter->setFixNamesByLogs($list);
        is_callable($callback) && $callback($this->runtimeFixCounter->migrationCount, 'setCount');
        $this->runtimeFixCounter->activeFixName = '';
        $this->runtimeFixCounter->fixNumber = 0;
        $this->runtimeFixCounter->time = microtime(true);
        foreach ($list as $log) {
            $processName = $log->processName;
            if ($processName == self::SPECIAL_PROCESS_SCENARIO) {
                continue;
            }
            $log->delete();
            if (!$log->success) {
                continue;
            }
            $this->runFixCallback($callback, $log->description, $log);
            try {
                $processName = $log->processName;
                $process = $this->getProcess($processName);
                $subjectHandler = $this->getSubjectHandler($log->subjectName);
                $process->rollback($subjectHandler, $log);
            } catch (\Exception $e) {
                continue;
            }
        }
        if ($this->runtimeFixCounter->fixNumber > 0 && $log) {
            $callbackData = array(
                'time' => microtime(true) - $this->runtimeFixCounter->time,
                'log' => $log,
                'error' => '',
            );
            is_callable($callback) && $callback($callbackData, 'end');
        }

        $this->_enableListen();

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
                    include $this->_getScenariosDir().DIRECTORY_SEPARATOR.$class.'.php';
                }
                if (!is_subclass_of($class, '\WS\Migrations\ScriptScenario')) {
                    continue;
                }
                $data = $log->updateData;
                /** @var ScriptScenario $object */
                $object = new $class($data, $this->getReferenceController());

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
                'error' => $error
            );
            is_callable($callback) && $callback($callbackData, 'end');
        }
    }

    /**
     * Value db version
     * @return PlatformVersion
     */
    public function getPlatformVersion() {
        if (!$this->version) {
            $this->version = new PlatformVersion($this->getOptions()->getOtherVersions());
        }
        return $this->version;
    }

    /**
     * Return owner db version
     * @return string
     */
    public function getVersionOwner() {
        return $this->getPlatformVersion()->getValue();
    }

    /**
     * Export data json format
     * @return string
     */
    public function getExportText() {
        $collector = $this->_createCollector();
        // version export
        foreach ($this->getReferenceController()->getItems() as $item) {
            $fix = $collector->createFix();
            $fix
                ->setName('Reference fix')
                ->setProcess(self::SPECIAL_PROCESS_FIX_REFERENCE)
                ->setUpdateData(array(
                    'reference' => $item->reference,
                    'group' => $item->group,
                    'dbVersion' => $item->dbVersion,
                    'id' => $item->id
                ));
            $collector->registerFix($fix);
        }

        // entities scheme export
        foreach ($this->getSubjectHandlers() as $handler) {
            $ids = $handler->existsIds();
            foreach ($ids as $id) {
                $snapshot = $handler->getSnapshot($id);
                $fix = $collector->createFix();
                $fix->setSubject(get_class($handler))
                    ->setProcess(self::SPECIAL_PROCESS_FULL_MIGRATE)
                    ->setDbVersion($this->getPlatformVersion()->getValue())
                    ->setUpdateData($snapshot);
                $collector->registerFix($fix);
            }
        }
        return arrayToJson($collector->getFixesData($this->getPlatformVersion()->getValue(), $this->getVersionOwner()));
    }

    /**
     * Refresh current DB version, copy references links
     */
    public function runRefreshVersion() {
        $platformVersion = $this->getPlatformVersion();
        $platformVersion->refresh();
        $this->getOptions()->dbPlatformVersion = $platformVersion->getValue();
        $registerResult = $this->getReferenceController()->setupNewVersion($platformVersion->getValue());
        if (!$registerResult) {
            return false;
        }
        $this->_referenceController = null;
        return true;
    }

    public function clearReferences() {
        $this->getReferenceController()->deleteAll();
    }

    public function install() {
        foreach ($this->getSubjectHandlers() as $handler) {
            $handler->registerExistsReferences();
        }
        $this->useDiagnostic()->run();
    }

    /**
     * @param CollectorFix $fix
     */
    private function _useAnotherVersion(CollectorFix $fix) {
        $this->_useVersion($fix->getDbVersion(), $fix->getOwner());
    }

    /**
     * @param string $dbVersion
     * @param string $owner
     */
    private function _useVersion($dbVersion, $owner) {
        if (!$owner) {
            return ;
        }
        $this->_versions = $this->_versions ?: $this->getOptions()->getOtherVersions();
        if (!$this->_versions[$dbVersion] || $this->_versions[$dbVersion] != $owner) {
            $this->_versions[$dbVersion] = $owner;
            $this->getOptions()->otherVersions = $this->_versions;
        }
    }

    /**
     * @param $dir
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\IO\FileNotFoundException
     */
    private function _getNotAppliedFiles($dir) {
        $result = AppliedChangesLogTable::getList(array(
            'select' => array('GROUP_LABEL'),
            'group' => array('GROUP_LABEL')
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
            $object = new $class(array(), $this->getReferenceController());
            $data = array(
                'name' => $object->name()
            );
            is_callable($callback) && $callback($data, 'start');

            $applyFixLog = new AppliedChangesLogModel();
            $applyFixLog->processName = self::SPECIAL_PROCESS_SCENARIO;
            $applyFixLog->subjectName = $class;
            $applyFixLog->setSetupLog($setupLog);
            $applyFixLog->groupLabel = $class.'.php';
            $applyFixLog->description = $object->name();
            list($dbVersion, $versionOwner) = $object->version();
            $applyFixLog->source = $dbVersion;
            $this->_useVersion($dbVersion, $versionOwner);
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
                'error' => $error
            );
            is_callable($callback) && $callback($data, 'end');
        }
        return count($classes);
    }

    /**
     * @param $callback
     * @param $fixName
     * @param $applyFixLog
     * @return mixed
     */
    private function runFixCallback($callback, $fixName, $applyFixLog) {
        if (!is_callable($callback)) {
            return false;
        }
        $prevFixName = $this->runtimeFixCounter->activeFixName;
        if ($prevFixName != $fixName && !empty($prevFixName)) {
            $data = array(
                'time' => microtime(true) - $this->runtimeFixCounter->time,
                'log' => $applyFixLog,
                'error' => ''
            );
            $callback($data, 'end');
        }

        if ($prevFixName != $fixName) {
            $this->runtimeFixCounter->fixNumber++;
            $this->runtimeFixCounter->time = microtime(true);
            $countFixes = $this->runtimeFixCounter->fixNames[$fixName . $this->runtimeFixCounter->fixNumber];
            $data = array(
                'name' => "{$fixName}" . ($countFixes > 1 ? "[{$countFixes}]" : ""),
            );
            $callback($data, 'start');
            $this->runtimeFixCounter->activeFixName = $fixName;
        }
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
