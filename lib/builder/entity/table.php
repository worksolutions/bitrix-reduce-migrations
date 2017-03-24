<?php

namespace WS\ReduceMigrations\Builder\Entity;


use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;

class Table {

    private $fields;

    public function __construct($name) {
        $this->name = $name;
    }

    private function addField($scalarField) {
        $field = new FieldWrapper($scalarField);
        $this->fields[$field->getName()] = $field;
        return $field;
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function string($name) {
        return $this->addField(new StringField($name));
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function integer($name) {
        return $this->addField(new IntegerField($name));
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function float($name) {
        return $this->addField(new FloatField($name));
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function datetime($name) {
        return $this->addField(new DatetimeField($name));
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function date($name) {
        return $this->addField(new DateField($name));
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function text($name) {
        return $this->addField(new TextField($name));
    }

    /**
     * @param $name
     *
     * @return FieldWrapper
     */
    public function boolean($name) {
        return $this->addField(new BooleanField($name));
    }

    /**
     * @return mixed
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getPrimary() {
        return array_filter(array_map(function (FieldWrapper $field) {
            return $field->isPrimary() ? $field->getName() : false;
        }, $this->getFields()));
    }

    /**
     * @return array
     */
    public function getAutoincrement() {
        return array_filter(array_map(function (FieldWrapper $field) {
            return $field->isAutoincrement() ? $field->getName() : false;
        }, $this->getFields()));
    }

    /**
     * @return array
     */
    public function getPreparedFields() {
        return array_map(function (FieldWrapper $field) {
            return $field->getField();
        }, $this->getFields());
    }

}
