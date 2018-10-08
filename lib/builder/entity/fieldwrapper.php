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
     * @param bool $unique
     *
     * @return $this
     */
    public function unique($unique = true) {
        $this->field->configureUnique($unique);

        return $this;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function required($required = true) {
        $this->field->configureRequired($required);

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

    /**
     * @return bool
     */
    public function isUnique() {
        return $this->field->isUnique();
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->field->isRequired();
    }

}
