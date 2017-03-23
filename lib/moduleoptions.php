<?php
namespace WS\ReduceMigrations;

/**
 * @property string $catalogPath
 * @property array $otherVersions
 * @property string $useAutotests
 * @property string $dbPlatformVersion
 * @author <sokolovsky@worksolutions.ru>
 */
final class ModuleOptions {
    private $_moduleName = 'ws.reducemigrations';

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
