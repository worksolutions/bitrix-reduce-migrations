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

    public function createAdminGatewayFile($adminFilePath, $gatewayFileName)
    {
        $adminFileRelativePath = str_replace(
            Application::getDocumentRoot(),
            '',
            $adminFilePath
        );
        $gatewayFilePath = Path::combine(
            Application::getDocumentRoot(),
            Application::getPersonalRoot(),
            'admin',
            $gatewayFileName
        );
        $gatewayFileContent = "<?php\r\nrequire_once \$_SERVER['DOCUMENT_ROOT'] . '{$adminFileRelativePath}';\r\n";
        file_put_contents($gatewayFilePath, $gatewayFileContent);
    }

    public function InstallFiles() {
        $this->createAdminGatewayFile(
            Path::combine(static::getModuleDir(), 'admin', 'controller.php'),
            'ws_reducemigrations.php'
        );

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
        $path = Application::getDocumentRoot() . '/' . Application::getPersonalRoot() . '/tools/migrate';
        $relativePath = str_replace(
            Application::getDocumentRoot(),
            '',
            Path::combine(static::getModuleDir(), 'admin', 'cli.php')
        );
        $fileContent = "<?php\r\n\$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../') . '/';\r\nrequire_once \$_SERVER['DOCUMENT_ROOT'] . '{$relativePath}';\r\n";
        file_put_contents($path, $fileContent);
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
