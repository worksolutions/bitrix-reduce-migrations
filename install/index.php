<?
use Bitrix\Main\Application;

include __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'include.php';

Class ws_migrations extends CModule {
    const MODULE_ID = 'ws.migrations';
    var $MODULE_ID = 'ws.migrations';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $PARTNER_NAME = 'WorkSolutions';
    var $PARTNER_URI = 'http://worksolutions.ru';
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct() {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('info');
        $this->MODULE_NAME = $localization->getDataByPath("name");
        $this->MODULE_DESCRIPTION = $localization->getDataByPath("description");
        $this->PARTNER_NAME = GetMessage('PARTNER_NAME');
        $this->PARTNER_NAME = $localization->getDataByPath("partner.name");
        $this->PARTNER_URI = 'http://worksolutions.ru';
    }

    function InstallDB($arParams = array()) {
        RegisterModuleDependences('main', 'OnPageStart', self::MODULE_ID, 'WS\Migrations\Module', 'listen');
        RegisterModuleDependences('main', 'OnAfterEpilog', self::MODULE_ID, 'WS\Migrations\Module', 'commitDutyChanges');
        global $DB;
        $DB->RunSQLBatch(Application::getDocumentRoot().'/'.Application::getPersonalRoot() . "/modules/".$this->MODULE_ID."/install/db/install.sql");
        return true;
    }

    function UnInstallDB($arParams = array()) {
        UnRegisterModuleDependences('main', 'OnPageStart', self::MODULE_ID, 'WS\Migrations\Module', 'listen');
        UnRegisterModuleDependences('main', 'OnAfterEpilog', self::MODULE_ID, 'WS\Migrations\Module', 'commitDutyChanges');
        global $DB;
        $DB->RunSQLBatch(Application::getDocumentRoot().'/'.Application::getPersonalRoot()."/modules/".$this->MODULE_ID."/install/db/uninstall.sql");
        return true;
    }

    function InstallFiles() {
        $rootDir = Application::getDocumentRoot().'/'.Application::getPersonalRoot();
        $adminGatewayFile = '/admin/ws_migrations.php';
        copy(__DIR__. $adminGatewayFile, $rootDir . $adminGatewayFile);
        return true;
    }

    function UnInstallFiles() {
        $rootDir = Application::getDocumentRoot().'/'.Application::getPersonalRoot();
        $adminGatewayFile = '/admin/ws_migrations.php';
        unlink($rootDir . $adminGatewayFile);
        return true;
    }

    function DoInstall() {
        global $APPLICATION, $data;
        $loc = \WS\ReduceMigrations\Module::getInstance()->getLocalization('setup');
        $options = \WS\ReduceMigrations\Module::getInstance()->getOptions();
        $this->createPlatformDirIfNotExists();
        global $errors;
        $errors = array();
        if ($data['catalog']) {
            $dir = $this->docRoot() . $data['catalog'];
            if (!is_dir($dir) && !$this->createDir($data['catalog'])) {
                $errors[] = $loc->getDataByPath('errors.notCreateDir');
            }
            if (!$errors) {
                $options->catalogPath = $data['catalog'];
                $this->InstallFiles();
                $this->InstallDB();
                RegisterModule(self::MODULE_ID);
                \Bitrix\Main\Loader::includeModule(self::MODULE_ID);
                \Bitrix\Main\Loader::includeModule('iblock');
                $this->module()->install();

                foreach ($this->module()->getSubjectHandlers() as $handler) {
                    $handlerClass = get_class($handler);
                    $handlerClassValue = (bool)$data['handlers'][$handlerClass];
                    $handlerClassValue && $this->module()->enableSubjectHandler($handlerClass);
                    !$handlerClassValue && $this->module()->disableSubjectHandler($handlerClass);
                }
                $this->createCli();
            }
        }
        if (!$data || $errors) {
            $APPLICATION->IncludeAdminFile($loc->getDataByPath('title'), __DIR__.'/form.php');
            return;
        }
    }

    function DoUninstall() {
        global $APPLICATION, $data;
        global $errors;
        $errors = array();
        $loc = $this->module()->getLocalization('uninstall');

        if (!$data || $errors) {
            $APPLICATION->IncludeAdminFile($loc->getDataByPath('title'), __DIR__.'/uninstall.php');
            return;
        }
        if ($data['removeAll'] == "Y") {
            $this->removeFiles();
            $this->UnInstallDB();
            $this->removeOptions();
            $this->removePlatformDir();
        }
        $this->UnInstallFiles();
        UnRegisterModule(self::MODULE_ID);
    }

    /**
     * @return \WS\ReduceMigrations\Module
     */
    private function module() {
        return WS\ReduceMigrations\Module::getInstance();
    }

    private function removeFiles() {
        $options = $this->module()->getOptions();
        $dir = $this->docRoot() .($options->catalogPath ?: 'migrations');
        is_dir($dir) && \Bitrix\Main\IO\Directory::deleteDirectory($dir);
        $this->removeCli();
    }

    private function removeOptions() {
        COption::RemoveOption("ws.migrations");
    }

    private function createCli() {
        $dest = Application::getDocumentRoot().'/'.Application::getPersonalRoot().'/tools';
        CopyDirFiles(__DIR__.'/tools', $dest, false, true);
    }

    private function removeCli() {
        unlink(Application::getDocumentRoot().Application::getPersonalRoot().'/tools/ws_migrations.php');
    }

    private function createPlatformDirIfNotExists() {
        $uploadDir = $this->docRoot() . \COption::GetOptionString("main", "upload_dir", "upload");
        if (is_dir($uploadDir.'/ws.migrations')) {
            return;
        }
        CopyDirFiles(__DIR__.'/upload', $uploadDir, false, true);
    }

    private function removePlatformDir() {
        $uploadDir = $this->docRoot() . \COption::GetOptionString("main", "upload_dir", "upload");
        \Bitrix\Main\IO\Directory::deleteDirectory($uploadDir.'/ws.migrations');
    }

    /**
     * @return mixed
     */
    private function docRoot() {
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/';
    }

    private function createDir($dir) {
        $parts = explode('/', $dir);
        $dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        foreach ($parts as $part) {
            if (!$part) {
                continue;
            }
            $dir .= '/'.$part;
            if (!mkdir($dir)) {
                return false;
            }
            chmod($dir, 0777);
        }
        return true;
    }
}
