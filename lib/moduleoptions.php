<?php
namespace WS\ReduceMigrations;

/**
 * @property string $catalogPath
 * @property array $otherVersions
 * @property string $useAutotests
 * @author <sokolovsky@worksolutions.ru>
 */
final class ModuleOptions {
    private $moduleName = 'ws.reducemigrations';

    private $cache = array();

    /**
     * @staticvar self $self
     * @return ModuleOptions
     */
    static public function getInstance() {
        static $self = null;
        if (!$self) {
            $self = new self;
        }
        return $self;
    }

    private function setToDb($name, $value) {
        \COption::SetOptionString($this->moduleName, $name, serialize($value));
    }

    private function getFromDb($name) {
        $value = \COption::GetOptionString($this->moduleName, $name);
        return unserialize($value);
    }

    public function __set($name, $value) {
        $this->setToCache($name, $value);
        $this->setToDb($name, $value);
        return $value;
    }

    public function __get($name) {
        $value = $this->getFormCache($name);
        if (is_null($value)) {
            $value = $this->getFromDb($name);
            $this->setToCache($name, $value);
        }
        return $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getFormCache($name) {
        return $this->cache[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    private function setToCache($name, $value) {
        $this->cache[$name] = $value;
    }
}
