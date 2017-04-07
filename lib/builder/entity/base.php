<?php

namespace WS\ReduceMigrations\Builder\Entity;


abstract class Base {
    /** @var array */
    protected $params;

    /**
     * @param string $attributeName
     * @param mixed $value
     *
     * @return Iblock
     */
    public function setAttribute($attributeName, $value) {
        $this->params[$attributeName] = $value;
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
}
