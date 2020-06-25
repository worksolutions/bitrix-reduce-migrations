<?php
use Bitrix\Main\Application;
use Bitrix\Main\IO\Path;

include __DIR__ . '/../include.php';

class ws_reducemigrations extends CModule{
    const MODULE_ID = 'ws.reducemigrations';
    var $MODULE_ID = 'ws.reducemigrations';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $PARTNER_NAME = 'WorkSolutions';
    var $PARTNER_URI = 'http://worksolutions.ru';
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    public function __construct() {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('info');
        $needToConvert = $this->needToConvertCharset();
        $this->MODULE_NAME = $this->message($localization->getDataByPath('name'), $needToConvert);
        $this->MODULE_DESCRIPTION = $this->message($localization->getDataByPath('description'), $needToConvert);
        $this->PARTNER_NAME = GetMessage('PARTNER_NAME');
        $this->PARTNER_NAME = $this->message($localization->getDataByPath('partner.name'), $needToConvert);
        $this->PARTNER_URI = 'http://worksolutions.ru';
    }

    private function needToConvertCharset() {
        return (LANG_CHARSET === 'UTF-8' || LANG_CHARSET === 'utf-8') && !$this->isUtfLangFiles();
    }

    private function message($message, $needToConvert) {
        if ($needToConvert) {
            return iconv('Windows-1251', 'UTF-8', $message);
        }
        return $message;
    }

    public function InstallDB($arParams = array()) {
        global $DB;
        $DB->RunSQLBatch(self::getModuleDir() . '/install/db/install.sql');

        return true;
    }

    public function UnInstallDB($arParams = array()) {
        global $DB;
        $DB->RunSQLBatch(self::getModuleDir() .  '/install/db/uninstall.sql');

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
        if ($this->needToConvertCharset()) {
            $this->convertLangFilesToUtf();
        }
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

    /**
     * @return mixed|string
     */
    public function isUtfLangFiles() {
        $localization = new \WS\ReduceMigrations\Localization(include static::getModuleDir() . '/lang/ru/info.php');
        return mb_detect_encoding($localization->getDataByPath('encoding'), 'UTF-8, Windows-1251') === 'UTF-8';
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
        if ($data['removeAll'] === 'Y') {
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
     * @return bool|string
     */
    public static function getModuleDir() {
        return dirname(Path::normalize(__DIR__).'../');
    }

    public function convertLangFilesToUtf() {
        /** @var CMain $APPLICATION */
        global $APPLICATION;
        $di = new RecursiveDirectoryIterator(static::getModuleDir() . '/lang/ru');
        /** @var SplFileInfo $fileInfo */
        foreach ($di as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            $content = file_get_contents($fileInfo->getRealPath());
            $convertedContent = $APPLICATION->ConvertCharset($content, 'windows-1251', 'UTF-8');
            file_put_contents($fileInfo->getRealPath(), $convertedContent);
        }
    }

    /**
     * @return mixed
     */
    private function docRoot() {
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/';
    }

    /**
     * @param string $dir
     * @return bool
     */
    private function createDir($dir) {
        return mkdir(
            Path::combine($this->docRoot(), $dir),
            BX_DIR_PERMISSIONS,
            true
        );
    }
}
