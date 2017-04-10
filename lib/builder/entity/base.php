<?php

namespace WS\ReduceMigrations\Builder\Entity;


abstract class Base {
    /** @var array */
    protected $params;
    protected $isDirty;

    /**
     * @param string $attributeName
     * @param mixed $value
     *
     * @return static
     */
    public function setAttribute($attributeName, $value) {
        $this->params[$attributeName] = $value;
        $this->markDirty();
        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($name) {
        return $this->params[$name];
    }
    /**
     * @return array
     */
    public function getData() {
        return $this->params;
    }

    protected abstract function getMap();

    public function __call($name, $arguments) {
        $map = $this->getMap();
        if (!isset($map[$name])) {
            throw new \Exception("Call to undefined method {$name}");
        }
        $this->setAttribute($map[$name], $arguments[0]);
        return $this;
    }

    public function isDirty() {
        return $this->isDirty;
    }

    public function markDirty() {
        $this->isDirty = true;
    }

    public function markClean() {
        $this->isDirty = false;
    }
}
