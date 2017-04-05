<?php

namespace WS\ReduceMigrations\Builder\Entity;


class Base {
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
}
