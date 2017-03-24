<?php

namespace WS\ReduceMigrations\Builder\Entity;


use Bitrix\Main\Entity\ScalarField;

class FieldWrapper {

    private $name;
    private $field;
    /** @var  boolean */
    private $autoincrement;
    /** @var  boolean */
    private $primary;


    public function __construct(ScalarField $field) {
        $this->name = strtoupper($field->getColumnName());
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param bool $increment
     *
     * @return $this
     */
    public function autoincrement($increment = true) {
        $this->autoincrement = $increment;
        return $this;
    }

    /**
     * @param bool $primary
     *
     * @return $this
     */
    public function primary($primary = true) {
        $this->primary = $primary;

        return $this;
    }

    /**
     * @return ScalarField
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @return bool
     */
    public function isAutoincrement() {
        return $this->autoincrement;
    }

    /**
     * @return bool
     */
    public function isPrimary() {
        return $this->primary;
    }

}
