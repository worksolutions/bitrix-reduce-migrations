<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class HighLoadBlock
 *
 * @method HighLoadBlock name(string $value)
 * @method HighLoadBlock tableName(string $value)
 *
 * @package WS\ReduceMigrations\Builder\Entity
 */
class HighLoadBlock extends Base {

    private $id;
    private $fields;

    public function __construct($name, $tableName, $id = false) {
        $this->id = $id;
        $this->name($name);
        $this->tableName($tableName);
    }

    public function getMap() {
        return array(
            'name' => 'NAME',
            'tableName' => 'TABLE_NAME',
        );
    }

    /**
     * @param int $id
     * @return HighLoadBlock
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
     * @param $code
     * @return UserField
     */
    public function addField($code) {
        $field = new UserField($code);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @param $code
     * @return UserField
     * @throws BuilderException
     */
    public function updateField($code) {
        $data = $this->findField($code);
        $field = new UserField($code);
        $field->setId($data['ID']);
        $field->markClean();
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @param $code
     * @return array
     * @throws BuilderException
     */
    private function findField($code) {
        if (!$this->getId()) {
            throw new BuilderException('Set higloadBlock for continue');
        }
        $field = \CUserTypeEntity::GetList(null, array(
            'FIELD_NAME' => $code,
            'ENTITY_ID' => "HLBLOCK_" . $this->getId(),
        ))->Fetch();

        if (empty($field)) {
            throw new BuilderException("Field for `$code` not found");
        }
        return $field;
    }

    /**
     * @return UserField[]
     */
    public function getFields() {
        return $this->fields;
    }

}