<?php

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class HighLoadBlock
 * @property int id
 * @property string name
 * @property string tableName
 * @package WS\Migrations\Builder\Entity
 */
class HighLoadBlock extends Base {

    public function __construct($name, $tableName, $id = false) {
        $this->name = $name;
        $this->tableName = $tableName;
        $this->id = $id;
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'name' => 'NAME',
            'tableName' => 'TABLE_NAME',
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
     * @param string $name
     * @return HighLoadBlock
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $tableName
     * @return HighLoadBlock
     */
    public function setTableName($tableName) {
        $this->tableName = $tableName;
        return $this;
    }

}