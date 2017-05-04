<?php
use Bitrix\Main\Application;

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include.php';

class ws_reducemigrations extends CModule{
    const MODULE_ID = 'ws.reducemigrations';
    public $MODULE_ID = 'ws.reducemigrations';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $PARTNER_NAME = 'WorkSolutions';
    public $PARTNER_URI = 'http://worksolutions.ru';
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $strError = '';

    public function __construct() {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('info');
        $this->MODULE_NAME = $localization->getDataByPath('name');
        $this->MODULE_DESCRIPTION = $localization->getDataByPath('description');
        $this->PARTNER_NAME = $localization->getDataByPath('partner.name');
        $this->PARTNER_URI = 'http://worksolutions.ru';
    }

    public function InstallDB($arParams = array()) {
        global $DB;
        $DB->RunSQLBatch(Application::getDocumentRoot() . '/' . Application::getPersonalRoot() . '/modules/' . $this->MODULE_ID . '/install/db/install.sql');

        return true;
    }

    public function UnInstallDB($arParams = array()) {
        global $DB;
        $DB->RunSQLBatch(Application::getDocumentRoot() . '/' . Application::getPersonalRoot() . '/modules/' . $this->MODULE_ID . '/install/db/uninstall.sql');

        return true;
    }

    public function InstallFiles() {
        $rootDir = Application::getDocumentRoot() . '/' . Application::getPersonalRoot();
        $adminGatewayFile = '/admin/ws_reducemigrations.php';
        copy(__DIR__ . $adminGatewayFile, $rootDir . $adminGatewayFile);

        return true;
    }

    public function UnInstallFiles() {
        $rootDir = Application::getDocumentRoot() . '/' . Application::getPersonalRoot();
        $adminGatewayFile = '/admin/ws_reducemigrations.php';
        unlink($rootDir . $adminGatewayFile);

        return true;
    }

    public function DoInstall($extendData = array()) {
        global $APPLICATION, $data;
        $loc = \WS\ReduceMigrations\Module::getInstance()->getLocalization('setup');
        $options = \WS\ReduceMigrations\Module::getInstance()->getOptions();
        global $errors;
        $data = array_merge((array)$data, $extendData);
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

                $this->createCli();
                RegisterModuleDependences('main', 'OnCheckListGet', self::MODULE_ID, \WS\ReduceMigrations\Tests\Starter::className(), 'items');
            }
        }
        if (!$data || $errors) {
            $APPLICATION->IncludeAdminFile($loc->getDataByPath('title'), __DIR__ . '/form.php');

            return;
        }
    }

    public function DoUninstall() {
        global $APPLICATION, $data;
        global $errors;
        $errors = array();
        $loc = $this->module()->getLocalization('uninstall');

        if (!$data || $errors) {
            $APPLICATION->IncludeAdminFile($loc->getDataByPath('title'), __DIR__ . '/uninstall.php');

            return;
        }
        if ($data['removeAll'] == 'Y') {
            $this->removeFiles();
            $this->UnInstallDB();
            $this->removeOptions();
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
        $dir = $this->docRoot() . ($options->catalogPath ?: 'reducemigrations');
        is_dir($dir) && \Bitrix\Main\IO\Directory::deleteDirectory($dir);
        $this->removeCli();
    }

    private function removeOptions() {
        COption::RemoveOption('ws.reducemigrations');
    }

    private function createCli() {
        $dest = Application::getDocumentRoot() . '/' . Application::getPersonalRoot() . '/tools';
        CopyDirFiles(__DIR__ . '/tools', $dest, false, true);
    }

    private function removeCli() {
        unlink(Application::getDocumentRoot() . Application::getPersonalRoot() . '/tools/migrate');
    }

    /**
     * @return mixed
     */
    private function docRoot() {
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/';
    }

    private function createDir($dir) {
        $parts = explode('/', $dir);
        $dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        foreach ($parts as $part) {
            if (!$part) {
                continue;
            }
            $dir .= '/' . $part;
            if (!mkdir($dir)) {
                return false;
            }
            chmod($dir, 0777);
        }

        return true;
    }
}
