<?php

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class EnumVariant
 * @property int id
 * @property string value
 * @property string xmlId
 * @property int sort
 * @property string default
 * @property string del
 * @package WS\ReduceMigrations\Builder\Entity
 */
class EnumVariant extends Base {

    public function __construct($value, $data = false) {
        $this->value = $value;
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'value' => 'VALUE',
            'xmlId' => 'XML_ID',
            'sort' => 'SORT',
            'default' => 'DEF',
            'del' => 'DEL',
        );
    }

    /**
     * @param int $id
     * @return IblockType
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $value
     * @return EnumVariant
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $xmlId
     * @return EnumVariant
     */
    public function setXmlId($xmlId) {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @param int $sort
     * @return EnumVariant
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param bool $default
     * @return EnumVariant
     */
    public function setDefault($default) {
        $this->default = $default ? 'Y' : 'N';
        return $this;
    }
}