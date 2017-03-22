<?php
namespace WS\ReduceMigrations;
use Bitrix\Main\Config\Configuration;

/**
 * @property string $catalogPath
 * @property array $otherVersions
 * @property string $useAutotests
 * @property string $enabledSubjectHandlers
 * @property string $dbPlatformVersion
 * @author <sokolovsky@worksolutions.ru>
 */
final class ModuleOptions {
    private $_moduleName = 'ws.migrations';

    private $_cache = array();

    private function __construct() {
    }

    /**
     * @staticvar self $self
     * @return Options
     */
    static public function getInstance() {
        static $self = null;
        if (!$self) {
            $self = new self;
        }
        return $self;
    }

    private function _setToDb($name, $value) {
        \COption::SetOptionString($this->_moduleName, $name, serialize($value));
    }

    private function _getFromDb($name) {
        $value = \COption::GetOptionString($this->_moduleName, $name);
        return unserialize($value);
    }

    public function __set($name, $value) {
        $this->_setToCache($name, $value);
        $this->_setToDb($name, $value);
        return $value;
    }

    public function __get($name) {
        $value = $this->_getFormCache($name);
        if (is_null($value)) {
            $value = $this->_getFromDb($name);
            $this->_setToCache($name, $value);
        }
        return $value;
    }

    /**
     * @param $class
     */
    public function disableSubjectHandler($class) {
        $this->enabledSubjectHandlers = array_diff($this->enabledSubjectHandlers ?: array(), array($class));
    }

    /**
     * @param $class
     */
    public function enableSubjectHandler($class) {
        $this->enabledSubjectHandlers = array_unique(array_merge($this->enabledSubjectHandlers ?: array(), array($class)));
    }

    /**
     * @param $class
     * @return bool
     */
    public function isEnableSubjectHandler($class) {
        return in_array($class, $this->enabledSubjectHandlers);
    }

    /**
     * @return array
     */
    public function getOtherVersions() {
        return $this->otherVersions;
    }

    /**
     * @param $name
     * @return mixed
     */
    private function _getFormCache($name) {
        return $this->_cache[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    private function _setToCache($name, $value) {
        $this->_cache[$name] = $value;
    }
}
